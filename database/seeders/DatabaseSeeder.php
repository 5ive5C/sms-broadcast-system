<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. Order matters: roles before clients
     * (clients clone the client-admin template), clients before billing/
     * campaigns (both hang off client + wallet + sender IDs).
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $superAdmin = Role::query()->where('slug', 'super-admin')->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Admin Faridah', 'client_id' => null, 'role_id' => $superAdmin->id, 'password' => Hash::make('password')],
        );

        $this->call([
            PricingTierSeeder::class,
            ClientSeeder::class,
            CampaignSeeder::class,
            BillingSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}
