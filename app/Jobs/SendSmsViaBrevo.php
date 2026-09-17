<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Wallet;
use App\Models\WalletLedgerEntry;
use App\Services\BrevoSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendSmsViaBrevo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(protected int $messageId)
    {
    }

    public function handle(BrevoSmsService $brevo): void
    {
        $message = Message::find($this->messageId);

        if (! $message || $message->status !== 'submitted') {
            return;
        }

        $result = $brevo->send(
            recipient: $message->recipient,
            content: $message->content,
            type: $message->lane === 'bulk' ? 'marketing' : 'transactional',
            tag: $message->campaign_id ? 'campaign-'.$message->campaign_id : $message->lane,
        );

        if ($result['accepted']) {
            // Brevo only confirms it accepted the send for onward delivery;
            // there is no delivery webhook wired up in this proof of concept,
            // so the message stays "submitted" — matching what the real
            // gateway response actually tells us at this point.
            return;
        }

        $this->markFailed($message, $result['error']);
    }

    protected function markFailed(Message $message, ?string $reason): void
    {
        DB::transaction(function () use ($message, $reason) {
            $wallet = Wallet::where('client_id', $message->client_id)->lockForUpdate()->firstOrFail();

            WalletLedgerEntry::post($wallet, 'refund', 'Refund — rejected by gateway ('.$message->code.')', (float) $message->parts, $message);

            $message->update([
                'status' => 'failed',
                'failure_reason' => $reason ?? 'Rejected by gateway',
                'credit_refunded' => $message->parts,
                'final_at' => now(),
            ]);

            if ($message->campaign_id) {
                $message->campaign()->update([
                    'failed_count' => DB::raw('failed_count + 1'),
                    'submitted_count' => DB::raw('GREATEST(submitted_count - 1, 0)'),
                    'credits_spent' => DB::raw('credits_spent - '.(int) $message->parts),
                ]);
            }
        });
    }
}
