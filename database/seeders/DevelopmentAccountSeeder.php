<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentAccountSeeder extends Seeder
{
    public function run(): void
    {
        $password = (string) config('healo.demo_password', 'password');

        $this->upsertUser('HEALO Admin', 'admin@healo.test', UserRole::Admin, $password);
        $this->upsertUser('Demo User', 'demo@healo.test', UserRole::User, $password);
    }

    private function upsertUser(string $name, string $email, UserRole $role, string $password): User
    {
        $user = User::withTrashed()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'role' => $role,
            'locale' => 'id',
            'email_verified_at' => now(),
            'password' => Hash::make($password),
            'deleted_at' => null,
        ])->save();

        return $user;
    }
}
