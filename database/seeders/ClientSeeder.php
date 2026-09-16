<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\PricingTier;
use App\Models\Role;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $tier10k = PricingTier::where('min_credits', 10000)->first();
        $tier1k = PricingTier::where('min_credits', 1000)->first();

        $maybank = $this->makeClient('Maybank Berhad', $tier10k, 'active', [
            'company_reg_no' => '196001000142',
            'industry' => 'Banking',
            'message_types' => ['tac', 'transactional', 'bulk'],
            'two_factor_required' => true,
            'pic_name' => 'Siti Rahman',
            'pic_phone' => '+60123456700',
            'pic_email' => 'siti.rahman@maybank.com',
        ], balance: 482310);

        $koperasi = $this->makeClient('Koperasi Sejahtera', $tier1k, 'active', [
            'industry' => 'Cooperative',
            'message_types' => ['transactional', 'bulk'],
            'pic_name' => 'Ahmad Zulkifli',
            'pic_phone' => '+60123456701',
            'pic_email' => 'admin@kopsejahtera.my',
        ], balance: 6940);

        $prima = $this->makeClient('Klinik Prima Group', $tier1k, 'pending', [
            'company_reg_no' => '201801023456',
            'industry' => 'Healthcare',
            'message_types' => ['transactional', 'bulk'],
            'two_factor_required' => true,
            'pic_name' => 'Dr. Amira Yusof',
            'pic_phone' => '+60123456702',
            'pic_email' => 'amira@klinikprima.my',
        ], balance: 0);

        $zaman = $this->makeClient('Zaman Logistics', $tier1k, 'suspended', [
            'industry' => 'Logistics',
            'message_types' => ['transactional'],
            'pic_name' => 'Farid Zaman',
            'pic_phone' => '+60123456703',
            'pic_email' => 'farid@zamanlogistics.my',
        ], balance: 1120);

        $this->seedMaybankTeam($maybank);
        $this->seedDefaultAdmin($koperasi, 'Ahmad Zulkifli', 'admin@kopsejahtera.my');
        $this->seedDefaultAdmin($prima, 'Dr. Amira Yusof', 'amira@klinikprima.my');
        $this->seedDefaultAdmin($zaman, 'Farid Zaman', 'farid@zamanlogistics.my');
    }

    protected function makeClient(string $name, ?PricingTier $tier, string $status, array $extra, int $balance): Client
    {
        $client = Client::query()->updateOrCreate(['slug' => \Illuminate\Support\Str::slug($name)], [
            'name' => $name,
            'pricing_tier_id' => $tier?->id,
            'status' => $status,
            ...$extra,
        ]);

        Wallet::query()->updateOrCreate(['client_id' => $client->id], ['balance' => $balance]);

        return $client;
    }

    /**
     * Maybank gets the richer, multi-role team the deck walks through
     * (Screens 06/09/10) — everyone else gets a single default admin.
     */
    protected function seedMaybankTeam(Client $maybank): void
    {
        $roles = [
            'client-admin' => ['name' => 'Client Admin', 'permissions' => array_keys(Role::PERMISSIONS)],
            'campaign-officer' => ['name' => 'Campaign Officer', 'permissions' => ['campaigns.manage', 'recipients.upload', 'quick-send.manage', 'reports.view']],
            'reports-only' => ['name' => 'Reports Only', 'permissions' => ['reports.view']],
            'finance' => ['name' => 'Finance', 'permissions' => ['top-ups.request', 'wallet.manage', 'reports.view']],
        ];

        $roleModels = [];
        foreach ($roles as $slug => $attrs) {
            $roleModels[$slug] = Role::query()->updateOrCreate(
                ['client_id' => $maybank->id, 'slug' => $slug],
                ['name' => $attrs['name'], 'permissions' => $attrs['permissions']],
            );
        }

        $users = [
            ['name' => 'Siti Rahman', 'email' => 'siti.rahman@maybank.com', 'role' => 'client-admin', '2fa' => true],
            ['name' => 'Hafiz Aziz', 'email' => 'hafiz.aziz@maybank.com', 'role' => 'campaign-officer', '2fa' => true],
            ['name' => 'Lim Wei', 'email' => 'lim.wei@maybank.com', 'role' => 'reports-only', '2fa' => false],
            ['name' => 'Nurul Aini binti Kamal', 'email' => 'nurul.aini@maybank.com', 'role' => 'finance', '2fa' => true],
        ];

        foreach ($users as $u) {
            $user = User::query()->updateOrCreate(['email' => $u['email']], [
                'name' => $u['name'],
                'client_id' => $maybank->id,
                'role_id' => $roleModels[$u['role']]->id,
                'password' => Hash::make('password'),
            ]);

            $user->forceFill(['two_factor_confirmed_at' => $u['2fa'] ? now() : null])->save();
        }
    }

    protected function seedDefaultAdmin(Client $client, string $name, string $email): void
    {
        $adminRole = Role::query()->updateOrCreate(
            ['client_id' => $client->id, 'slug' => 'client-admin'],
            ['name' => 'Client Admin', 'permissions' => array_keys(Role::PERMISSIONS)],
        );

        User::query()->updateOrCreate(['email' => $email], [
            'name' => $name,
            'client_id' => $client->id,
            'role_id' => $adminRole->id,
            'password' => Hash::make('password'),
        ]);
    }
}
