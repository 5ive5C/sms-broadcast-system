<?php

namespace Database\Seeders;

use App\Models\PricingTier;
use Illuminate\Database\Seeder;

class PricingTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            ['name' => '1,000 credits', 'min_credits' => 1000, 'price_per_credit' => 0.120, 'is_active' => true],
            ['name' => '10,000 credits', 'min_credits' => 10000, 'price_per_credit' => 0.110, 'is_active' => true],
            ['name' => '100,000 credits', 'min_credits' => 100000, 'price_per_credit' => 0.095, 'is_active' => true],
            ['name' => '500,000 credits', 'min_credits' => 500000, 'price_per_credit' => 0.090, 'is_active' => false],
        ];

        foreach ($tiers as $tier) {
            PricingTier::query()->updateOrCreate(['min_credits' => $tier['min_credits']], $tier);
        }
    }
}
