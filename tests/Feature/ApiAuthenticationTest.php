<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_through_v1_api(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'a-secure-password',
            'password_confirmation' => 'a-secure-password',
            'device_name' => 'feature-test',
            'locale' => 'id',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'demo@example.com')
            ->assertJsonPath('data.role', 'user')
            ->assertJsonStructure(['data', 'token']);
    }

    public function test_user_can_login_and_read_own_api_profile(): void
    {
        User::factory()->create(['email' => 'demo@example.com']);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'demo@example.com',
            'password' => 'password',
            'device_name' => 'feature-test',
        ])->assertOk();

        $this->withToken($login->json('token'))
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.email', 'demo@example.com');
    }

    public function test_invalid_api_credentials_are_rejected_without_a_token(): void
    {
        User::factory()->create(['email' => 'demo@example.com']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'demo@example.com',
            'password' => 'wrong-password',
            'device_name' => 'feature-test',
        ])->assertUnprocessable()->assertJsonMissingPath('token');
    }

    public function test_api_profile_requires_authentication(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
    }
}
