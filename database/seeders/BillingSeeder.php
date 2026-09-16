<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\PricingTier;
use App\Models\TopUpRequest;
use App\Models\WalletLedgerEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        $maybank = Client::where('slug', 'maybank-berhad')->firstOrFail();
        $koperasi = Client::where('slug', 'koperasi-sejahtera')->firstOrFail();
        $prima = Client::where('slug', 'klinik-prima-group')->firstOrFail();
        $admin = \App\Models\User::where('email', 'superadmin@example.com')->first();

        $tier100k = PricingTier::where('min_credits', 100000)->firstOrFail();
        $tier10k = PricingTier::where('min_credits', 10000)->firstOrFail();
        $tier1k = PricingTier::where('min_credits', 1000)->firstOrFail();

        // Pending — Maybank, 100,000 credits (matches the deck's Screen 09/10 walkthrough).
        TopUpRequest::create([
            'client_id' => $maybank->id,
            'pricing_tier_id' => $tier100k->id,
            'credits' => 100000,
            'price_per_credit' => $tier100k->price_per_credit,
            'total_amount' => 100000 * $tier100k->price_per_credit,
            'status' => 'pending',
            'created_at' => Carbon::parse('2026-09-12 09:30'),
        ]);

        // Pending — Klinik Prima, 1,000 credits.
        TopUpRequest::create([
            'client_id' => $prima->id,
            'pricing_tier_id' => $tier1k->id,
            'credits' => 1000,
            'price_per_credit' => $tier1k->price_per_credit,
            'total_amount' => 1000 * $tier1k->price_per_credit,
            'status' => 'pending',
            'created_at' => Carbon::parse('2026-09-12 08:10'),
        ]);

        // Approved — Koperasi Sejahtera, 10,000 credits.
        TopUpRequest::create([
            'client_id' => $koperasi->id,
            'pricing_tier_id' => $tier10k->id,
            'credits' => 10000,
            'price_per_credit' => $tier10k->price_per_credit,
            'total_amount' => 10000 * $tier10k->price_per_credit,
            'status' => 'approved',
            'approved_by' => $admin?->id,
            'approved_at' => Carbon::parse('2026-09-05 11:00'),
            'created_at' => Carbon::parse('2026-09-04 16:20'),
        ]);

        $this->seedMaybankLedgerAndInvoices($maybank);
    }

    /**
     * Historical ledger rows and invoices matching Screens 11/12 exactly.
     * Posted directly at their historical dates/balances rather than via
     * WalletLedgerEntry::post(), which always stamps "now".
     */
    protected function seedMaybankLedgerAndInvoices(Client $maybank): void
    {
        $wallet = $maybank->wallet;

        $entries = [
            ['date' => '2026-08-08 10:00', 'type' => 'topup', 'description' => 'Top-up approved — TOP-2026-0388', 'change' => 45000, 'balance_after' => 93156],
            ['date' => '2026-09-08 09:00', 'type' => 'topup', 'description' => 'Top-up approved — TOP-2026-0414', 'change' => 500000, 'balance_after' => 538156],
            ['date' => '2026-09-11 23:59', 'type' => 'api', 'description' => 'API sends — TAC/OTP daily aggregate', 'change' => -31884, 'balance_after' => 506272],
            ['date' => '2026-09-12 08:00', 'type' => 'refund', 'description' => 'Refund — 38 messages rejected by iSMS', 'change' => 38, 'balance_after' => 506310],
            ['date' => '2026-09-12 09:41', 'type' => 'campaign', 'description' => 'Campaign — Raya statement reminder', 'change' => -24000, 'balance_after' => 482310],
        ];

        foreach ($entries as $entry) {
            WalletLedgerEntry::create([
                'client_id' => $maybank->id,
                'wallet_id' => $wallet->id,
                'type' => $entry['type'],
                'description' => $entry['description'],
                'change' => $entry['change'],
                'balance_after' => $entry['balance_after'],
                'created_at' => $entry['date'],
                'updated_at' => $entry['date'],
            ]);
        }

        $invoices = [
            ['no' => 'INV-2026-0388', 'date' => '2026-08-21', 'credits' => 45000 / 0.10, 'price' => 0.10, 'status' => 'paid'],
            ['no' => 'INV-2026-0414', 'date' => '2026-09-08', 'credits' => 500000, 'price' => 0.09, 'status' => 'paid'],
            ['no' => 'INV-2026-0418', 'date' => '2026-09-12', 'credits' => 100000, 'price' => 0.095, 'status' => 'unpaid'],
        ];

        foreach ($invoices as $inv) {
            $amount = $inv['credits'] * $inv['price'];

            Invoice::create([
                'client_id' => $maybank->id,
                'invoice_no' => $inv['no'],
                'invoice_date' => $inv['date'],
                'credits' => $inv['credits'],
                'price_per_credit' => $inv['price'],
                'amount' => $amount,
                'sst' => 0,
                'total' => $amount,
                'status' => $inv['status'],
                'created_at' => $inv['date'],
                'updated_at' => $inv['date'],
            ]);
        }
    }
}
