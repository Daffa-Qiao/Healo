<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'HEALO Admin',
            'email' => 'admin@healo.test',
            'role' => UserRole::Admin,
        ]);

        User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@healo.test',
            'role' => UserRole::User,
        ]);
    }
}
