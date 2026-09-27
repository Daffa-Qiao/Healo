<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_is_audited_without_raw_ip_address(): void
    {
        User::factory()->create(['email' => 'demo@example.com']);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post('/login', ['email' => 'demo@example.com', 'password' => 'password']);

        $log = AuditLog::query()->where('action', 'auth.login')->firstOrFail();

        $this->assertSame(64, strlen($log->ip_hash));
        $this->assertStringNotContainsString('203.0.113.10', $log->ip_hash);
        $this->assertNull($log->safe_metadata);
    }
}
