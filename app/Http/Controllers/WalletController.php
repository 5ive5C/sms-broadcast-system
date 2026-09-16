<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTopUpRequest;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\PricingTier;
use App\Models\TopUpRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    /**
     * Screen 11 — current balance plus every credit and debit.
     */
    public function index(Request $request): View
    {
        $client = $request->user()->actingClient();
        $wallet = $client->wallet;

        $usedThisMonth = $wallet->ledgerEntries()
            ->whereIn('type', ['campaign', 'quick_send', 'api'])
            ->whereMonth('created_at', now()->month)
            ->sum('change');

        $refunded = $wallet->ledgerEntries()
            ->where('type', 'refund')
            ->whereMonth('created_at', now()->month)
            ->sum('change');

        return view('wallet.index', [
            'wallet' => $wallet,
            'entries' => $wallet->ledgerEntries()->latest()->paginate(20),
            'usedThisMonth' => abs($usedThisMonth),
            'refunded' => $refunded,
        ]);
    }

    /**
     * Screen 09 — request top-up form.
     */
    public function topUp(Request $request): View
    {
        return view('wallet.top-up', [
            'wallet' => $request->user()->actingClient()->wallet,
            'tiers' => PricingTier::query()->active()->orderBy('min_credits')->get(),
        ]);
    }

    /**
     * Submits the request against a payment slip. Credit is only added
     * once an internal admin confirms payment (Screen 10).
     */
    public function requestTopUp(StoreTopUpRequest $request): RedirectResponse
    {
        $client = $request->user()->actingClient();
        $credits = $request->validated('credits');

        $tier = PricingTier::query()->active()->where('min_credits', '<=', $credits)
            ->orderByDesc('min_credits')->first()
            ?? PricingTier::query()->active()->orderBy('min_credits')->first();

        $pricePerCredit = $tier?->price_per_credit ?? 0.12;
        $slipPath = $request->file('slip')->store('topup-slips');

        $topUp = TopUpRequest::create([
            'client_id' => $client->id,
            'pricing_tier_id' => $tier?->id,
            'credits' => $credits,
            'price_per_credit' => $pricePerCredit,
            'total_amount' => round($credits * $pricePerCredit, 2),
            'slip_path' => $slipPath,
            'status' => 'pending',
        ]);

        AuditLog::record($request->user(), 'Top-up requested', $topUp->reference().' · '.number_format($credits).' credits');

        return redirect()->route('wallet.top-up')
            ->with('status', $topUp->reference().' submitted. Credit is added once payment is confirmed.');
    }

    /**
     * Screen 12 — downloadable record of every top-up.
     */
    public function invoices(Request $request): View
    {
        $invoices = Invoice::query()
            ->where('client_id', $request->user()->actingClient()->id)
            ->latest('invoice_date')
            ->get();

        return view('wallet.invoices', ['invoices' => $invoices]);
    }
}
