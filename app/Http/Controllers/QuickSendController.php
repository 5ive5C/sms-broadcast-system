<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuickSendRequest;
use App\Models\AuditLog;
use App\Models\Message;
use App\Models\WalletLedgerEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuickSendController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, \Closure $next) {
            \Illuminate\Support\Facades\Gate::authorize('quick-send.manage');

            return $next($request);
        });
    }

    /**
     * Ad-hoc sends without setting up a campaign, capped well below the
     * campaign wizard's recipient ceiling.
     */
    protected const MAX_RECIPIENTS = 500;

    /**
     * Screen 14 — type a message, paste phone numbers, send.
     */
    public function create(Request $request): View
    {
        $client = $request->user()->actingClient();

        return view('quick-send.index', [
            'wallet' => $client->wallet,
        ]);
    }

    /**
     * Store a batch of one-off messages. There is no campaign row — Quick
     * Send is intentionally lighter weight than the campaign wizard.
     */
    public function store(StoreQuickSendRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $client = $actor->actingClient();

        $recipients = $this->parseRecipients(
            $request->input('recipients_text'),
            $request->file('recipients_file'),
        );

        if (empty($recipients)) {
            throw ValidationException::withMessages([
                'recipients_text' => 'No valid phone numbers were found. Use 8-15 digits, one per line (or comma-separated).',
            ]);
        }

        if (count($recipients) > self::MAX_RECIPIENTS) {
            throw ValidationException::withMessages([
                'recipients_text' => 'Quick send is capped at '.self::MAX_RECIPIENTS.' recipients (found '.count($recipients).'). Use a campaign for larger sends.',
            ]);
        }

        $content = $request->validated('content');
        ['encoding' => $encoding, 'parts' => $parts] = Message::smsParts($content);
        $creditsRequired = count($recipients) * $parts;

        $wallet = $client->wallet;

        if ($wallet->balance < $creditsRequired) {
            throw ValidationException::withMessages([
                'recipients_text' => 'Not enough balance: this send needs '.number_format($creditsRequired).' credits, wallet has '.number_format($wallet->balance).'.',
            ]);
        }

        DB::transaction(function () use ($recipients, $client, $content, $encoding, $parts) {
            $wallet = \App\Models\Wallet::where('client_id', $client->id)->lockForUpdate()->firstOrFail();

            $failedCount = 0;

            foreach ($recipients as $recipient) {
                [$status, $failureReason, $finalAt] = $this->simulateOutcome();
                $failedCount += $status === 'failed' ? 1 : 0;

                Message::create([
                    'client_id' => $client->id,
                    'lane' => 'transactional',
                    'recipient' => $recipient,
                    'content' => $content,
                    'encoding' => $encoding,
                    'parts' => $parts,
                    'status' => $status,
                    'credit_charged' => $parts,
                    'credit_refunded' => $status === 'failed' ? $parts : 0,
                    'failure_reason' => $failureReason,
                    'submitted_at' => now(),
                    'final_at' => $finalAt,
                ]);
            }

            // Failed sends are refunded automatically, so only the
            // delivered/submitted portion is a net debit against the wallet.
            $netCredits = (count($recipients) - $failedCount) * $parts;

            WalletLedgerEntry::post(
                $wallet,
                'quick_send',
                'Quick send — '.count($recipients).' recipient(s)',
                -$netCredits,
            );
        });

        AuditLog::record($actor, 'Quick send', count($recipients).' recipient(s)');

        return redirect()->route('quick-send.create')
            ->with('status', 'Sent to '.number_format(count($recipients)).' recipient(s).');
    }

    /**
     * There is no real telco behind this proof of concept — outcomes are
     * drawn from a realistic delivered/submitted/failed mix instead of a
     * live gateway response.
     *
     * @return array{0: string, 1: ?string, 2: ?\Illuminate\Support\Carbon}
     */
    protected function simulateOutcome(): array
    {
        $roll = mt_rand(1, 100);

        if ($roll <= 92) {
            return ['delivered', null, now()->addSeconds(mt_rand(1, 60))];
        }

        if ($roll <= 97) {
            return ['submitted', null, null];
        }

        return ['failed', 'Handset unreachable', now()->addMinutes(mt_rand(1, 10))];
    }

    /**
     * Parse recipient phone numbers out of pasted text and/or an uploaded
     * txt/csv file. Lenient by design: splits on newlines/commas/semicolons,
     * strips everything but digits and a leading '+', and keeps only tokens
     * that look like a phone number.
     *
     * @return array<int, string>
     */
    protected function parseRecipients(?string $text, ?\Illuminate\Http\UploadedFile $file): array
    {
        $raw = $file ? (string) file_get_contents($file->getRealPath()) : (string) $text;

        $tokens = preg_split('/[\r\n,;]+/', $raw) ?: [];

        $numbers = [];

        foreach ($tokens as $token) {
            $cleaned = preg_replace('/[^\d+]/', '', trim($token));

            if ($cleaned && preg_match('/^\+?\d{8,15}$/', $cleaned)) {
                $numbers[] = $cleaned;
            }
        }

        return array_values(array_unique($numbers));
    }
}
