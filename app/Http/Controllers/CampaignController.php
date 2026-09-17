<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampaignRequest;
use App\Models\AuditLog;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Wallet;
use App\Models\WalletLedgerEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CampaignController extends Controller
{
    /**
     * A campaign can have at most this many recipients.
     */
    protected const MAX_RECIPIENTS = 100000;

    public function __construct()
    {
        $this->middleware(function (Request $request, \Closure $next) {
            Gate::authorize('campaigns.manage');

            return $next($request);
        });
    }

    /**
     * Screen 15 — all bulk campaigns with their state, recipient count and
     * delivery summary.
     */
    public function index(Request $request): View
    {
        $campaigns = Campaign::query()
            ->where('client_id', $request->user()->actingClient()->id)
            ->with('creator')
            ->when($request->filled('status') && $request->status !== 'all', fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('campaigns.index', ['campaigns' => $campaigns, 'status' => $request->input('status', 'all')]);
    }

    /**
     * Step 1 — Compose (Screen 17).
     */
    public function create(Request $request): View
    {
        $clientId = $request->user()->actingClient()->id;

        return view('campaigns.create', [
            'templates' => MessageTemplate::query()->where('client_id', $clientId)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $actor = $request->user();

        $campaign = Campaign::create([
            'client_id' => $actor->actingClient()->id,
            'template_id' => $request->validated('template_id'),
            'created_by' => $actor->id,
            'name' => $request->validated('name'),
            'content' => $request->validated('content'),
            'lane' => $request->validated('lane'),
            'status' => 'draft',
        ]);

        if ($request->validated('template_id')) {
            MessageTemplate::whereKey($request->validated('template_id'))->update(['last_used_at' => now()]);
        }

        return redirect()->route('campaigns.recipients', $campaign);
    }

    /**
     * Step 2 — Recipients (Screen 18). Only a phone list is accepted here;
     * templates with merge placeholders skip straight past this into the
     * merge step instead (Screen 19), since a phone-only list can't supply
     * merge values.
     */
    public function recipients(Campaign $campaign): View
    {
        $this->authorizeCampaign($campaign);

        $placeholders = MessageTemplate::extractPlaceholders($campaign->content);

        return view('campaigns.recipients', ['campaign' => $campaign, 'placeholders' => $placeholders]);
    }

    public function storeRecipients(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);

        $placeholders = MessageTemplate::extractPlaceholders($campaign->content);

        if ($placeholders) {
            return $this->storeMergeUpload($request, $campaign, $placeholders);
        }

        $request->validate([
            'recipients_text' => ['nullable', 'string', 'required_without:recipients_file'],
            'recipients_file' => ['nullable', 'file', 'extensions:txt,csv', 'max:5120', 'required_without:recipients_text'],
        ]);

        $file = $request->file('recipients_file');
        $raw = $file ? (string) file_get_contents($file->getRealPath()) : (string) $request->input('recipients_text');
        $tokens = preg_split('/[\r\n,;]+/', $raw) ?: [];

        [$accepted, $rejected] = $this->validateRecipients($tokens);

        if (empty($accepted)) {
            throw ValidationException::withMessages([
                'recipients_text' => 'No valid recipients were found.',
            ]);
        }

        if (count($accepted) > self::MAX_RECIPIENTS) {
            throw ValidationException::withMessages([
                'recipients_text' => 'A campaign can have at most '.number_format(self::MAX_RECIPIENTS).' recipients.',
            ]);
        }

        $campaign->update([
            'recipient_source' => $file ? 'upload' : 'paste',
            'recipient_file_name' => $file?->getClientOriginalName(),
            'pending_recipients' => array_map(fn ($phone) => ['phone' => $phone], $accepted),
            'rejected_rows' => $rejected,
            'recipient_count' => count($accepted),
            'rejected_count' => count($rejected),
        ]);

        return redirect()->route('campaigns.review', $campaign);
    }

    /**
     * Step 3 — Merge fields (Screen 19). Column-to-placeholder mapping for
     * an uploaded file, shown only when the compose step's content has
     * {placeholder} tokens.
     */
    public function merge(Campaign $campaign): View
    {
        $this->authorizeCampaign($campaign);

        abort_unless($campaign->recipient_source === 'merge', 404);

        $staged = $campaign->pending_recipients ?? ['header' => [], 'rows' => []];
        $placeholders = MessageTemplate::extractPlaceholders($campaign->content);

        return view('campaigns.merge', [
            'campaign' => $campaign,
            'placeholders' => $placeholders,
            'header' => $staged['header'],
            'rowCount' => count($staged['rows']),
        ]);
    }

    public function storeMerge(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);

        abort_unless($campaign->recipient_source === 'merge', 404);

        $placeholders = MessageTemplate::extractPlaceholders($campaign->content);
        $staged = $campaign->pending_recipients ?? ['header' => [], 'rows' => []];

        $request->validate([
            'phone_column' => ['required', 'integer'],
            'columns' => ['required', 'array'],
        ]);

        $phoneColumn = (int) $request->input('phone_column');
        $columns = $request->input('columns'); // placeholder => column index

        $ready = [];
        $missing = [];

        foreach ($staged['rows'] as $i => $row) {
            $phone = $this->normalizePhone($row[$phoneColumn] ?? '');

            if (! $phone) {
                $missing[] = ['row' => $i + 2, 'reason' => 'Missing or invalid phone number'];
                continue;
            }

            $values = ['phone' => $phone];
            $rowMissing = false;

            foreach ($placeholders as $placeholder) {
                $colIndex = $columns[$placeholder] ?? null;
                $value = $colIndex !== null ? trim((string) ($row[(int) $colIndex] ?? '')) : '';

                if ($value === '') {
                    $rowMissing = true;
                }

                $values[$placeholder] = $value;
            }

            if ($rowMissing) {
                $missing[] = ['row' => $i + 2, 'reason' => 'Missing merge value'];
                continue;
            }

            $ready[] = $values;
        }

        $campaign->update([
            'merge_mapping' => ['phone' => $phoneColumn, ...$columns],
            'pending_recipients' => $ready,
            'rejected_rows' => $missing,
            'recipient_count' => count($ready),
            'rejected_count' => count($missing),
        ]);

        return redirect()->route('campaigns.review', $campaign);
    }

    /**
     * Step 4 — Confirm and Launch (Screen 20) / Progress (Screen 21) once
     * launched.
     */
    public function review(Campaign $campaign): View
    {
        $this->authorizeCampaign($campaign);

        if ($campaign->status !== 'draft') {
            return view('campaigns.show', ['campaign' => $campaign->load('messages')]);
        }

        $recipients = $campaign->pending_recipients ?? [];
        $longest = collect($recipients)
            ->map(fn ($row) => $this->renderContent($campaign->content, $row))
            ->map(fn ($text) => Message::smsParts($text)['parts'])
            ->max() ?? 1;

        $creditsRequired = count($recipients) * max($longest, 1);
        $wallet = $campaign->client->wallet;

        return view('campaigns.review', [
            'campaign' => $campaign,
            'recipientCount' => count($recipients),
            'creditsRequired' => $creditsRequired,
            'balanceAfter' => $wallet->balance - $creditsRequired,
            'preview' => $this->renderContent($campaign->content, $recipients[0] ?? []),
        ]);
    }

    public function launch(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);
        abort_unless($campaign->isDraft(), 409);

        $recipients = $campaign->pending_recipients ?? [];

        if (empty($recipients)) {
            throw ValidationException::withMessages(['recipients' => 'No recipients to send to.']);
        }

        $actor = $request->user();
        $scheduledAt = $request->filled('scheduled_at') ? $request->date('scheduled_at') : null;

        DB::transaction(function () use ($campaign, $recipients, $actor, $scheduledAt) {
            $wallet = Wallet::where('client_id', $campaign->client_id)->lockForUpdate()->firstOrFail();

            $creditsRequired = 0;

            foreach ($recipients as $row) {
                $content = $this->renderContent($campaign->content, $row);
                ['encoding' => $encoding, 'parts' => $parts] = Message::smsParts($content);

                Message::create([
                    'client_id' => $campaign->client_id,
                    'campaign_id' => $campaign->id,
                    'lane' => $campaign->lane,
                    'recipient' => $row['phone'],
                    'content' => $content,
                    'encoding' => $encoding,
                    'parts' => $parts,
                    'status' => 'submitted',
                    'credit_charged' => $parts,
                    'credit_refunded' => 0,
                    'submitted_at' => now(),
                    'scheduled_for' => $scheduledAt,
                ]);

                $creditsRequired += $parts;
            }

            // Full amount is debited up front; the dispatch-pending poller
            // (app:dispatch-pending-sms) picks these rows up — immediately,
            // or once scheduled_for is reached — and queues the gateway job,
            // which refunds any recipient it ends up rejecting.
            WalletLedgerEntry::post(
                $wallet,
                'campaign',
                'Campaign — '.$campaign->name,
                -$creditsRequired,
                $campaign,
            );

            $campaign->update([
                'status' => $scheduledAt ? 'scheduled' : 'sending',
                'scheduled_at' => $scheduledAt,
                'launched_at' => $scheduledAt ? null : now(),
                'launched_by' => $actor->id,
                'delivered_count' => 0,
                'submitted_count' => count($recipients),
                'failed_count' => 0,
                'credits_required' => $creditsRequired,
                'credits_spent' => $creditsRequired,
                'pending_recipients' => null,
            ]);
        });

        AuditLog::record($actor, 'Campaign launched', $campaign->name.' · '.count($recipients).' recipients');

        return redirect()->route('campaigns.show', $campaign)->with('status', 'Campaign launched.');
    }

    public function saveDraft(Campaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);

        return redirect()->route('campaigns.index')->with('status', 'Campaign saved as draft.');
    }

    /**
     * Screen 21 — live dispatch progress with delivery breakdown.
     */
    public function show(Campaign $campaign): View
    {
        $this->authorizeCampaign($campaign);

        return view('campaigns.show', ['campaign' => $campaign->load('messages')]);
    }

    protected function authorizeCampaign(Campaign $campaign): void
    {
        abort_unless($campaign->client_id === request()->user()->actingClient()?->id, 403);
    }

    /**
     * Parse an uploaded XLSX/CSV into a header row + data rows, staged on
     * the campaign for the merge step's column mapping UI.
     */
    protected function storeMergeUpload(Request $request, Campaign $campaign, array $placeholders): RedirectResponse
    {
        $request->validate([
            'recipients_file' => ['required', 'file', 'extensions:csv,txt,xlsx', 'max:5120'],
        ]);

        $file = $request->file('recipients_file');
        $rows = $this->parseDelimited($file);

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'recipients_file' => 'The file needs a header row plus at least one data row.',
            ]);
        }

        $header = array_shift($rows);

        $campaign->update([
            'recipient_source' => 'merge',
            'recipient_file_name' => $file->getClientOriginalName(),
            'pending_recipients' => ['header' => $header, 'rows' => $rows],
            'rejected_rows' => null,
            'recipient_count' => 0,
            'rejected_count' => 0,
        ]);

        return redirect()->route('campaigns.merge', $campaign);
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function parseDelimited(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        while ($handle && ($row = fgetcsv($handle)) !== false) {
            $rows[] = array_map('trim', $row);
        }

        if ($handle) {
            fclose($handle);
        }

        return $rows;
    }

    /**
     * @param  array<int, string>  $tokens
     * @return array{0: array<int, string>, 1: array<int, array<string, mixed>>}
     */
    protected function validateRecipients(array $tokens): array
    {
        $accepted = [];
        $rejected = [];
        $seen = [];

        foreach ($tokens as $i => $token) {
            $trimmed = trim($token);

            if ($trimmed === '') {
                continue;
            }

            $cleaned = preg_replace('/[^\d+]/', '', $trimmed);
            $digitsOnly = ltrim($cleaned, '+');

            if (strlen($digitsOnly) < 9) {
                $rejected[] = ['row' => $i + 1, 'value' => $trimmed, 'reason' => 'Too short'];

                continue;
            }

            if (! str_starts_with($digitsOnly, '60')) {
                $rejected[] = ['row' => $i + 1, 'value' => $trimmed, 'reason' => 'Outside allowed country'];

                continue;
            }

            $normalized = '+'.$digitsOnly;

            if (isset($seen[$normalized])) {
                $rejected[] = ['row' => $i + 1, 'value' => $trimmed, 'reason' => 'Duplicate of row '.$seen[$normalized]];

                continue;
            }

            $seen[$normalized] = $i + 1;
            $accepted[] = $normalized;
        }

        return [$accepted, $rejected];
    }

    protected function normalizePhone(string $raw): ?string
    {
        $cleaned = preg_replace('/[^\d+]/', '', trim($raw));
        $digitsOnly = ltrim((string) $cleaned, '+');

        if (strlen($digitsOnly) < 9 || ! str_starts_with($digitsOnly, '60')) {
            return null;
        }

        return '+'.$digitsOnly;
    }

    /**
     * Substitute {placeholder} tokens in the campaign content with a
     * recipient row's merge values.
     *
     * @param  array<string, mixed>  $row
     */
    protected function renderContent(string $content, array $row): string
    {
        return preg_replace_callback('/\{(\w+)\}/', fn ($m) => $row[$m[1]] ?? $m[0], $content);
    }
}
