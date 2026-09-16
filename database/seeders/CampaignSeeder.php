<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Client;
use App\Models\Message;
use App\Models\MessageAttempt;
use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CampaignSeeder extends Seeder
{
    /**
     * Message volume here is a representative sample, not a literal replay
     * of the deck's monthly totals (108k+ TAC messages alone) — enough
     * rows to make the log/report/dashboard screens feel real without a
     * multi-minute seed. Campaign-level counters (recipient/delivered/
     * failed counts) are set to the exact figures shown on the slides
     * regardless of how many individual Message rows back them.
     */
    public function run(): void
    {
        $maybank = Client::where('slug', 'maybank-berhad')->firstOrFail();
        $hafiz = $maybank->users()->where('email', 'hafiz.aziz@maybank.com')->firstOrFail();
        $siti = $maybank->users()->where('email', 'siti.rahman@maybank.com')->firstOrFail();

        $templates = $this->seedTemplates($maybank);
        $this->seedFlagshipMessages($maybank);
        $this->seedDailyTraffic($maybank);

        $raya = Campaign::create([
            'client_id' => $maybank->id,
            'template_id' => $templates['Statement ready']->id,
            'created_by' => $hafiz->id,
            'launched_by' => $hafiz->id,
            'name' => 'Raya statement reminder',
            'content' => "Dear customer, your statement for Sep 2026 is ready. View it in the app or at maybank2u.com.my. Do not share your TAC with anyone.",
            'lane' => 'bulk',
            'status' => 'sending',
            'recipient_source' => 'upload',
            'recipient_count' => 23962,
            'rejected_count' => 38,
            'delivered_count' => 21086,
            'submitted_count' => 2157,
            'failed_count' => 719,
            'credits_required' => 23962,
            'credits_spent' => 23243,
            'launched_at' => Carbon::parse('2026-09-12 09:41'),
        ]);
        $this->sampleCampaignMessages($raya, 600, ['delivered' => 88, 'submitted' => 9, 'failed' => 3]);

        $cardRenewal = Campaign::create([
            'client_id' => $maybank->id,
            'created_by' => $hafiz->id,
            'launched_by' => $hafiz->id,
            'name' => 'Card renewal notice',
            'content' => 'Hi {name}, your card ending {card_last4} will be renewed automatically. No action is needed.',
            'lane' => 'bulk',
            'status' => 'completed',
            'recipient_source' => 'merge',
            'recipient_count' => 12000,
            'delivered_count' => 11540,
            'submitted_count' => 240,
            'failed_count' => 220,
            'credits_required' => 12000,
            'credits_spent' => 11780,
            'launched_at' => Carbon::parse('2026-08-21 10:15'),
        ]);
        $this->sampleCampaignMessages($cardRenewal, 300, ['delivered' => 96, 'submitted' => 2, 'failed' => 2]);

        Campaign::create([
            'client_id' => $maybank->id,
            'created_by' => $siti->id,
            'name' => 'Branch closure notice — Johor',
            'content' => 'Notice: our {branch} branch will be closed on {date} for scheduled maintenance.',
            'lane' => 'transactional',
            'status' => 'scheduled',
            'recipient_source' => 'merge',
            'recipient_count' => 4310,
            'scheduled_at' => now()->addDays(2),
        ]);

        Campaign::create([
            'client_id' => $maybank->id,
            'created_by' => $hafiz->id,
            'name' => 'Fixed deposit promo',
            'content' => 'Enjoy preferential FD rates this month. Visit your nearest branch or the app to place a placement.',
            'lane' => 'bulk',
            'status' => 'draft',
            'recipient_count' => 31884,
        ]);

        // Pad the list out to a realistic count (Screen 15 shows "27 campaigns").
        $padNames = [
            'Year-end statement reminder', 'Festive greeting', 'App update notice', 'Fraud alert reminder',
            'Loan offer', 'Insurance renewal', 'Branch relocation notice', 'Password policy update',
            'KYC reminder', 'Cashback promo', 'Overdraft notice', 'Scam awareness alert',
            'Mobile banking tips', 'Interest rate update', 'Credit limit review', 'Statement e-delivery push',
            'Referral campaign', 'Holiday hours notice', 'System maintenance notice', 'Loyalty points expiry',
            'New branch opening', 'Travel notice reminder', 'Survey invitation',
        ];

        foreach ($padNames as $i => $name) {
            $delivered = random_int(500, 15000);
            Campaign::create([
                'client_id' => $maybank->id,
                'created_by' => $hafiz->id,
                'launched_by' => $hafiz->id,
                'name' => $name,
                'content' => 'Message content for '.$name.'.',
                'lane' => 'bulk',
                'status' => 'completed',
                'recipient_source' => 'paste',
                'recipient_count' => $delivered + random_int(10, 200),
                'delivered_count' => $delivered,
                'submitted_count' => 0,
                'failed_count' => random_int(5, 150),
                'credits_required' => $delivered,
                'credits_spent' => $delivered,
                'launched_at' => now()->subDays(random_int(3, 90)),
            ]);
        }
    }

    /**
     * @return array<string, MessageTemplate>
     */
    protected function seedTemplates(Client $maybank): array
    {
        $rows = [
            'Statement ready' => [
                'body' => 'Dear {name}, your statement for {month} is ready. View it in the app or at maybank2u.com.my.',
                'last_used_at' => now()->subDays(2),
            ],
            'Payment due reminder' => [
                'body' => 'Hi {name}, your payment of RM{amount} is due on {due_date}. Pay via the app to avoid a late charge.',
                'last_used_at' => now()->subDays(6),
            ],
            'Card renewal notice' => [
                'body' => 'Hi {name}, your card ending {card_last4} will be renewed automatically. No action is needed.',
                'last_used_at' => now()->subDays(24),
            ],
            'Branch closure notice' => [
                'body' => 'Notice: our {branch} branch will be closed on {date} for scheduled maintenance.',
                'last_used_at' => now()->subDays(43),
            ],
        ];

        $templates = [];

        foreach ($rows as $name => $attrs) {
            $templates[$name] = MessageTemplate::query()->updateOrCreate(
                ['client_id' => $maybank->id, 'name' => $name],
                [
                    'body' => $attrs['body'],
                    'placeholders' => MessageTemplate::extractPlaceholders($attrs['body']),
                    'parts' => 1,
                    'last_used_at' => $attrs['last_used_at'],
                ],
            );
        }

        return $templates;
    }

    /**
     * A handful of messages seeded with the exact codes/content the deck's
     * message log, delivery report, detail and reconciliation screens
     * walk through, so browsing to them shows the same story as the deck.
     */
    protected function seedFlagshipMessages(Client $maybank): void
    {
        $rayaContent = "Dear customer, your statement for Sep 2026 is ready. View it in the app or at maybank2u.com.my. Do not share your TAC with anyone.";

        $delivered = Message::create([
            'code' => 'msg_9fa21c', 'client_id' => $maybank->id, 'lane' => 'tac',
            'recipient' => '+60123456781', 'content' => '', 'parts' => 1, 'status' => 'delivered',
            'submitted_at' => Carbon::parse('2026-09-13 09:11'), 'final_at' => Carbon::parse('2026-09-13 09:12'),
        ]);

        Message::create([
            'code' => 'msg_9fa21d', 'client_id' => $maybank->id, 'lane' => 'tac',
            'recipient' => '+60198765404', 'content' => '', 'parts' => 1, 'status' => 'submitted',
            'submitted_at' => Carbon::parse('2026-09-13 09:11'),
        ]);

        $failed = Message::create([
            'code' => 'msg_9f8730', 'client_id' => $maybank->id, 'lane' => 'bulk',
            'recipient' => '+60173344527', 'content' => $rayaContent, 'parts' => 1, 'status' => 'failed',
            'credit_refunded' => 1, 'failure_reason' => 'iSMS rejected — invalid subscriber',
            'isms_status' => 'delivered',
            'submitted_at' => Carbon::parse('2026-09-12 09:41'), 'final_at' => Carbon::parse('2026-09-12 18:33:40'),
        ]);

        MessageAttempt::insert([
            ['message_id' => $failed->id, 'sequence' => 1, 'attempted_at' => '2026-09-12 18:31:04', 'result' => 'iSMS error 503 — gateway busy', 'created_at' => now(), 'updated_at' => now()],
            ['message_id' => $failed->id, 'sequence' => 2, 'attempted_at' => '2026-09-12 18:31:34', 'result' => 'iSMS error 503 — gateway busy', 'created_at' => now(), 'updated_at' => now()],
            ['message_id' => $failed->id, 'sequence' => 3, 'attempted_at' => '2026-09-12 18:33:39', 'result' => 'iSMS rejected — invalid subscriber', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Message::create([
            'code' => 'msg_9f8722', 'client_id' => $maybank->id, 'lane' => 'transactional',
            'recipient' => '+60112298855', 'content' => 'Reminder: your appointment at Maybank Jalan Ampang is tomorrow at 10:00 AM.',
            'parts' => 1, 'status' => 'delivered',
            'submitted_at' => Carbon::parse('2026-09-12 18:39'), 'final_at' => Carbon::parse('2026-09-12 18:39:30'),
        ]);

        Message::create([
            'code' => 'msg_9f86e1', 'client_id' => $maybank->id, 'lane' => 'bulk',
            'recipient' => '+60167223419', 'content' => $rayaContent, 'parts' => 1, 'status' => 'submitted',
            'submitted_at' => Carbon::parse('2026-09-12 09:41'),
        ]);

        // Handset-unreachable / not-in-service failures for the Failed &
        // Resend screen (Screen 26) — charged, not refunded.
        Message::insert([
            $this->row($maybank->id, 'transactional', '+60123456781', 'failed', now()->subDay(), 'Handset unreachable'),
            $this->row($maybank->id, 'transactional', '+60198765404', 'failed', now()->subDay(), 'Handset unreachable'),
            $this->row($maybank->id, 'transactional', '+60112298855', 'failed', now()->subDays(2), 'Number not in service'),
        ]);
    }

    protected function row(int $clientId, string $lane, string $recipient, string $status, Carbon $at, ?string $reason = null): array
    {
        return [
            'code' => 'msg_'.Str::lower(Str::random(6)),
            'client_id' => $clientId,
            'lane' => $lane,
            'recipient' => $recipient,
            'content' => 'Reminder: your appointment is tomorrow at 10:00 AM.',
            'encoding' => 'gsm7',
            'parts' => 1,
            'status' => $status,
            'credit_charged' => 1,
            'credit_refunded' => 0,
            'failure_reason' => $reason,
            'submitted_at' => $at,
            'final_at' => $at->copy()->addMinutes(random_int(1, 10)),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * TAC/OTP and transactional API traffic (no campaign attached) spread
     * across the last 7 days, weighted toward today, for the dashboard's
     * volume-by-hour chart and the message log/usage report to have
     * something real to show.
     */
    protected function seedDailyTraffic(Client $maybank): void
    {
        $this->bulkInsert($maybank->id, 'tac', 2200, ['delivered' => 98, 'submitted' => 1, 'failed' => 1], '');
        $this->bulkInsert($maybank->id, 'transactional', 600, ['delivered' => 96, 'submitted' => 2, 'failed' => 2], 'Reminder: your appointment is tomorrow at 10:00 AM.');
    }

    /**
     * @param  array{delivered:int, submitted:int, failed:int}  $weights  percentages
     */
    protected function sampleCampaignMessages(Campaign $campaign, int $count, array $weights): void
    {
        $rows = [];
        $start = $campaign->launched_at ?? now();

        for ($i = 0; $i < $count; $i++) {
            $status = $this->weightedStatus($weights);
            $at = $start->copy()->addSeconds(random_int(0, 720));

            $rows[] = [
                'code' => 'msg_'.Str::lower(Str::random(6)),
                'client_id' => $campaign->client_id,
                'campaign_id' => $campaign->id,
                'lane' => $campaign->lane,
                'recipient' => '+601'.random_int(10000000, 99999999),
                'content' => $campaign->content,
                'encoding' => 'gsm7',
                'parts' => 1,
                'status' => $status,
                'credit_charged' => 1,
                'credit_refunded' => $status === 'failed' ? 1 : 0,
                'failure_reason' => $status === 'failed' ? 'Handset unreachable' : null,
                'submitted_at' => $at,
                'final_at' => $status === 'submitted' ? null : $at->copy()->addMinutes(random_int(1, 30)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        collect($rows)->chunk(500)->each(fn ($chunk) => Message::insert($chunk->all()));
    }

    /**
     * @param  array{delivered:int, submitted:int, failed:int}  $weights  percentages
     */
    protected function bulkInsert(int $clientId, string $lane, int $count, array $weights, string $content): void
    {
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $status = $this->weightedStatus($weights);
            $daysAgo = random_int(0, 6);
            $at = $daysAgo === 0
                ? today()->addHours(random_int(0, 21))->addMinutes(random_int(0, 59))
                : now()->subDays($daysAgo)->setTime(random_int(8, 21), random_int(0, 59));

            $isMismatch = random_int(1, 500) === 1;

            $rows[] = [
                'code' => 'msg_'.Str::lower(Str::random(6)),
                'client_id' => $clientId,
                'lane' => $lane,
                'recipient' => '+601'.random_int(10000000, 99999999),
                'content' => $content,
                'encoding' => 'gsm7',
                'parts' => 1,
                'status' => $status,
                'credit_charged' => 1,
                'credit_refunded' => $status === 'failed' ? 1 : 0,
                'failure_reason' => $status === 'failed' ? 'Handset unreachable' : null,
                'isms_status' => $isMismatch ? ($status === 'delivered' ? 'failed' : 'no_record') : null,
                'submitted_at' => $at,
                'final_at' => $status === 'submitted' ? null : $at->copy()->addMinutes(random_int(1, 30)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        collect($rows)->chunk(500)->each(fn ($chunk) => Message::insert($chunk->all()));
    }

    protected function weightedStatus(array $weights): string
    {
        $roll = random_int(1, 100);
        $cumulative = 0;

        foreach ($weights as $status => $pct) {
            $cumulative += $pct;
            if ($roll <= $cumulative) {
                return $status;
            }
        }

        return 'delivered';
    }
}
