<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsViaBrevo;
use App\Models\Message;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:dispatch-pending-sms {--limit=500}')]
#[Description('Claim messages inserted by the portal/API and hand them to the Brevo send queue')]
class DispatchPendingSms extends Command
{
    /**
     * Runs on a schedule (see routes/console.php) so any code path that
     * inserts a "submitted" message — Quick Send, campaign launch, resend,
     * or a future API endpoint — only has to write the row. This command is
     * the single place that claims rows and queues the gateway job, so a
     * message is only ever picked up once even if two runs overlap.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $claimed = [];

        DB::transaction(function () use ($limit, &$claimed) {
            $ids = Message::query()
                ->awaitingDispatch()
                ->orderBy('id')
                ->limit($limit)
                ->lockForUpdate()
                ->pluck('id');

            if ($ids->isEmpty()) {
                return;
            }

            Message::whereIn('id', $ids)->update(['queued_at' => now()]);

            $claimed = $ids->all();
        });

        foreach ($claimed as $messageId) {
            SendSmsViaBrevo::dispatch($messageId);
        }

        $this->info(count($claimed).' message(s) queued for sending.');

        return self::SUCCESS;
    }
}
