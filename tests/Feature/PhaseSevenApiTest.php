<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseSevenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_api_requires_authentication(): void
    {
        $this->getJson('/api/v1/notifications')->assertUnauthorized();
        $this->getJson('/api/v1/notification-preferences')->assertUnauthorized();
    }

    public function test_user_can_list_count_and_mark_owned_notifications_read(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $notification = app(NotificationService::class)->create(
            $user,
            NotificationType::HealthPlan,
            'Rencana sehat',
            'Masih ada aktivitas yang belum selesai.',
            ['pending_count' => 1, 'internal_secret' => 'must-not-leak'],
            '/health-plan',
            today(),
            'daily-plan',
        );

        $this->getJson('/api/v1/notifications')->assertOk()
            ->assertJsonPath('data.0.id', $notification->id)
            ->assertJsonPath('data.0.message', 'Masih ada aktivitas yang belum selesai.')
            ->assertJsonMissing(['internal_secret' => 'must-not-leak']);
        $this->getJson('/api/v1/notifications/unread-count')->assertOk()->assertJsonPath('data.unread_count', 1);
        $this->postJson("/api/v1/notifications/{$notification->id}/read")->assertOk()->assertJsonPath('data.read_at', fn ($value) => is_string($value));
        $this->postJson('/api/v1/notifications/read-all')->assertOk()->assertJsonPath('data.unread_count', 0);
    }

    public function test_api_cannot_access_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $notification = app(NotificationService::class)->create($owner, NotificationType::System, 'Sistem', 'Pesan.', referenceKey: 'foreign');
        Sanctum::actingAs($attacker);

        $this->postJson("/api/v1/notifications/{$notification->id}/read")->assertForbidden();
    }

    public function test_user_can_read_and_update_notification_preferences_via_api(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/notification-preferences')->assertOk()
            ->assertJsonPath('data.notifications_enabled', true);
        $this->putJson('/api/v1/notification-preferences', [
            'notifications_enabled' => true,
            'health_plan_enabled' => true,
            'meal_reminder_enabled' => false,
            'journal_reminder_enabled' => true,
            'health_goal_enabled' => true,
            'health_insight_enabled' => true,
            'wellness_enabled' => true,
            'system_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
            'reminder_time' => '08:00',
        ])->assertOk()->assertJsonPath('data.meal_reminder_enabled', false);
    }
}
