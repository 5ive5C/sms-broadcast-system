<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        Role::query()->updateOrCreate(
            ['client_id' => null, 'slug' => 'super-admin'],
            ['name' => 'Super Admin', 'permissions' => ['*']],
        );

        Role::query()->updateOrCreate(
            ['client_id' => null, 'slug' => 'client-admin'],
            ['name' => 'Client Admin', 'permissions' => [
                'campaigns.manage',
                'api-keys.manage',
                'users.manage',
                'top-ups.request',
                'reports.view',
            ]],
        );

        Role::query()->updateOrCreate(
            ['client_id' => null, 'slug' => 'client-staff'],
            ['name' => 'Client Staff', 'permissions' => [
                'campaigns.manage',
                'reports.view',
            ]],
        );
    }
}
