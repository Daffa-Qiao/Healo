<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_center_is_authenticated_paginated_and_owner_scoped(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $service = app(NotificationService::class);
        $service->create($owner, NotificationType::Wellness, 'Milik pengguna', 'Pesan pribadi.', referenceKey: 'owned');
        $service->create($other, NotificationType::System, 'Milik pengguna lain', 'Tidak boleh terlihat.', referenceKey: 'foreign');

        $this->get('/notifications')->assertRedirect('/login');
        $this->actingAs($owner)->get('/notifications')->assertOk()
            ->assertSee('Milik pengguna')
            ->assertDontSee('Milik pengguna lain');
    }

    public function test_legacy_notification_feature_page_redirects_to_completed_center(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/features/notifications')
            ->assertRedirect(route('notifications.index'));
    }

    public function test_user_can_mark_owned_notifications_read_but_not_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $service = app(NotificationService::class);
        $owned = $service->create($owner, NotificationType::System, 'Sistem', 'Pesan.', referenceKey: 'owned');
        $foreign = $service->create($other, NotificationType::System, 'Sistem', 'Pesan.', referenceKey: 'foreign');

        $this->actingAs($owner)->post(route('notifications.read', $owned))->assertRedirect();
        $this->assertNotNull($owned->refresh()->read_at);

        $this->actingAs($owner)->post(route('notifications.read', $foreign))->assertForbidden();
        $this->assertNull($foreign->refresh()->read_at);
    }

    public function test_user_can_mark_all_owned_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $service = app(NotificationService::class);
        $service->create($user, NotificationType::System, 'Satu', 'Pesan.', referenceKey: 'one');
        $service->create($user, NotificationType::Wellness, 'Dua', 'Pesan.', referenceKey: 'two');

        $this->actingAs($user)->post('/notifications/read-all')->assertRedirect();

        $this->assertSame(0, $service->unreadCount($user));
    }

    public function test_user_can_update_notification_preferences_with_validated_times(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings/notifications', [
            'notifications_enabled' => '1',
            'health_plan_enabled' => '1',
            'meal_reminder_enabled' => '0',
            'journal_reminder_enabled' => '1',
            'health_goal_enabled' => '1',
            'health_insight_enabled' => '1',
            'wellness_enabled' => '1',
            'system_enabled' => '1',
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
            'reminder_time' => '08:30',
        ])->assertRedirect(route('notification-preferences.edit'));

        $preference = $user->notificationPreference()->firstOrFail();
        $this->assertFalse($preference->meal_reminder_enabled);
        $this->assertSame('22:00', substr($preference->quiet_hours_start, 0, 5));
        $this->assertSame('08:30', substr($preference->reminder_time, 0, 5));

        $this->actingAs($user)->from('/settings/notifications')->put('/settings/notifications', [
            'quiet_hours_start' => 'invalid',
            'quiet_hours_end' => '07:00',
            'reminder_time' => '30:99',
        ])->assertSessionHasErrors(['quiet_hours_start', 'reminder_time']);
    }
}
