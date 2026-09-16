<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePricingTierRequest;
use App\Models\PricingTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PricingTierController extends Controller
{
    /**
     * Screen 08 — price per credit by top-up size.
     */
    public function index(): View
    {
        return view('admin.pricing-tiers.index', [
            'tiers' => PricingTier::query()->withCount('clients')->orderBy('min_credits')->get(),
        ]);
    }

    /**
     * Add a tier. Applied automatically the next time a client tops up
     * at this size.
     */
    public function store(StorePricingTierRequest $request): RedirectResponse
    {
        PricingTier::create([
            'name' => number_format($request->validated('min_credits')).' credits',
            'min_credits' => $request->validated('min_credits'),
            'price_per_credit' => $request->validated('price_per_credit'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('pricing-tiers.index')->with('status', 'Pricing tier saved.');
    }

    /**
     * Toggle a tier active/draft, or edit its price.
     */
    public function update(StorePricingTierRequest $request, PricingTier $pricingTier): RedirectResponse
    {
        $pricingTier->update([
            'min_credits' => $request->validated('min_credits'),
            'price_per_credit' => $request->validated('price_per_credit'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('pricing-tiers.index')->with('status', 'Pricing tier updated.');
    }
}
