<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Message;
use App\Models\Wallet;
use App\Models\WalletLedgerEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, \Closure $next) {
            Gate::authorize('reports.view');

            return $next($request);
        });
    }

    /**
     * Screen 23 — every message with its status.
     */
    public function index(Request $request): View
    {
        $messages = $this->filtered($request)->latest('submitted_at')->paginate(25)->withQueryString();

        return view('messages.index', ['messages' => $messages]);
    }

    public function export(Request $request): StreamedResponse
    {
        $messages = $this->filtered($request)->orderByDesc('submitted_at')->get();

        return response()->streamDownload(function () use ($messages) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Message ID', 'Recipient', 'Type', 'Campaign', 'Status', 'Final At']);

            foreach ($messages as $message) {
                fputcsv($out, [
                    $message->code,
                    $message->maskedRecipient(),
                    $message->lane,
                    $message->campaign?->name ?? '',
                    ucfirst($message->status),
                    $message->final_at?->format('d M Y H:i') ?? 'awaiting telco',
                ]);
            }

            fclose($out);
        }, 'messages.csv');
    }

    /**
     * Screen 25 — full history of one message.
     */
    public function show(Request $request, Message $message): View
    {
        abort_unless($message->client_id === $request->user()->actingClient()?->id, 403);

        return view('messages.show', ['message' => $message->load(['attempts', 'campaign'])]);
    }

    /**
     * Screen 26 — rejected sends are refunded automatically; telco-level
     * failures can be resent as a new charged batch.
     */
    public function failed(Request $request): View
    {
        $client = $request->user()->actingClient();

        $failed = Message::query()
            ->where('client_id', $client->id)
            ->where('status', 'failed')
            ->where('lane', '!=', 'tac')
            ->when($request->filled('campaign_id'), fn ($q) => $q->where('campaign_id', $request->campaign_id))
            ->where('submitted_at', '>=', now()->subDays(7))
            ->latest('submitted_at')
            ->paginate(25)
            ->withQueryString();

        return view('messages.failed', [
            'messages' => $failed,
            'campaigns' => $client->campaigns()->orderByDesc('created_at')->limit(50)->get(),
        ]);
    }

    /**
     * Resend selected telco-level failures as a fresh, newly charged batch.
     * TAC/OTP is deliberately excluded — the client system issues a fresh
     * code instead of the portal resending an old one.
     */
    public function resend(Request $request): RedirectResponse
    {
        $request->validate(['message_ids' => ['required', 'array']]);

        $actor = $request->user();
        $client = $actor->actingClient();

        $originals = Message::query()
            ->whereKey($request->input('message_ids'))
            ->where('client_id', $client->id)
            ->where('status', 'failed')
            ->where('lane', '!=', 'tac')
            ->get();

        if ($originals->isEmpty()) {
            return redirect()->route('messages.failed')->with('error', 'Nothing eligible to resend.');
        }

        $creditsNeeded = (int) $originals->sum('parts');
        $wallet = $client->wallet;

        if ($wallet->balance < $creditsNeeded) {
            return redirect()->route('messages.failed')->with('error', 'Not enough balance to resend the selected messages.');
        }

        DB::transaction(function () use ($originals, $client, $creditsNeeded) {
            $wallet = Wallet::where('client_id', $client->id)->lockForUpdate()->firstOrFail();

            foreach ($originals as $original) {
                Message::create([
                    'client_id' => $client->id,
                    'campaign_id' => $original->campaign_id,
                    'lane' => $original->lane,
                    'recipient' => $original->recipient,
                    'content' => $original->content,
                    'encoding' => $original->encoding,
                    'parts' => $original->parts,
                    'status' => 'submitted',
                    'credit_charged' => $original->parts,
                    'credit_refunded' => 0,
                    'submitted_at' => now(),
                ]);
            }

            WalletLedgerEntry::post($wallet, 'campaign', 'Resend — '.$originals->count().' message(s)', -$creditsNeeded);
        });

        AuditLog::record($actor, 'Messages resent', $originals->count().' message(s)');

        return redirect()->route('messages.failed')->with('status', $originals->count().' message(s) resent.');
    }

    protected function filtered(Request $request)
    {
        $client = $request->user()->actingClient();

        return Message::query()
            ->where('client_id', $client->id)
            ->when($request->filled('type') && $request->type !== 'all', fn ($q) => $q->where('lane', $request->type))
            ->when($request->filled('status') && $request->status !== 'all', fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('days'), fn ($q) => $q->where('submitted_at', '>=', now()->subDays((int) $request->days)))
            ->with('campaign');
    }
}
