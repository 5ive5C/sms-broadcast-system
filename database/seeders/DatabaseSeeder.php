<?php

namespace Database\Seeders;

use App\Models\PortalUser;
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
        // PortalUser::factory(10)->create();

        PortalUser::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
