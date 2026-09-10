<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $client = Client::query()->updateOrCreate(
            ['slug' => 'demo-client'],
            ['name' => 'Demo Client', 'status' => 'active'],
        );

        $superAdmin = Role::query()->where('slug', 'super-admin')->firstOrFail();
        $clientAdmin = Role::query()->where('slug', 'client-admin')->firstOrFail();
        $clientStaff = Role::query()->where('slug', 'client-staff')->firstOrFail();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'client_id' => null,
            'role_id' => $superAdmin->id,
        ]);

        User::factory()->create([
            'name' => 'Client Admin',
            'email' => 'clientadmin@example.com',
            'client_id' => $client->id,
            'role_id' => $clientAdmin->id,
        ]);

        User::factory()->create([
            'name' => 'Client Staff',
            'email' => 'clientstaff@example.com',
            'client_id' => $client->id,
            'role_id' => $clientStaff->id,
        ]);
    }
}
