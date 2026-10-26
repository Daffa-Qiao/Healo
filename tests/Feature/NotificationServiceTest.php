<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\User;
use App\Services\Notification\NotificationPreferenceService;
use App\Services\Notification\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_notification_creation_is_idempotent_for_the_same_daily_reference(): void
    {
        $user = User::factory()->create();
        $service = app(NotificationService::class);

        $first = $service->create(
            $user,
            NotificationType::JournalReminder,
            'Jurnal kesehatan',
            'Luangkan beberapa menit untuk mencatat kondisi dan perasaanmu hari ini.',
            actionUrl: '/health-journals/create',
            referenceDate: today(),
            referenceKey: 'daily-journal',
        );
        $second = $service->create(
            $user,
            NotificationType::JournalReminder,
            'Jurnal kesehatan',
            'Pesan ini tidak boleh membuat notifikasi kedua.',
            actionUrl: '/health-journals/create',
            referenceDate: today(),
            referenceKey: 'daily-journal',
        );

        $this->assertNotNull($first);
        $this->assertTrue($first->is($second));
        $this->assertSame(1, $user->userNotifications()->count());
    }

    public function test_disabled_category_prevents_notification_creation(): void
    {
        $user = User::factory()->create();
        app(NotificationPreferenceService::class)->update($user, [
            'journal_reminder_enabled' => false,
        ]);

        $notification = app(NotificationService::class)->create(
            $user,
            NotificationType::JournalReminder,
            'Jurnal kesehatan',
            'Pengingat jurnal.',
            referenceDate: today(),
            referenceKey: 'daily-journal',
        );

        $this->assertNull($notification);
        $this->assertSame(0, $user->userNotifications()->count());
    }

    public function test_quiet_hours_defer_active_delivery_until_the_end_time(): void
    {
        Carbon::setTestNow('2026-09-30 23:15:00');
        $user = User::factory()->create();
        app(NotificationPreferenceService::class)->update($user, [
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
        ]);

        $notification = app(NotificationService::class)->create(
            $user,
            NotificationType::Wellness,
            'Wellness',
            'Jaga ritme sehatmu secara bertahap.',
            referenceDate: today(),
            referenceKey: 'evening-wellness',
        );

        $this->assertSame('2026-10-01 07:00:00', $notification?->available_at?->format('Y-m-d H:i:s'));
    }

    public function test_mark_read_and_mark_all_read_only_change_the_owners_notifications(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $service = app(NotificationService::class);
        $owned = $service->create($owner, NotificationType::System, 'Sistem', 'Pembaruan tersedia.', referenceKey: 'owner-system');
        $foreign = $service->create($other, NotificationType::System, 'Sistem', 'Pembaruan tersedia.', referenceKey: 'other-system');

        $service->markAsRead($owner, $owned);
        $this->assertNotNull($owned->refresh()->read_at);
        $this->assertNull($foreign->refresh()->read_at);

        $service->markAllAsRead($owner);
        $this->assertSame(0, $service->unreadCount($owner));
        $this->assertSame(1, $service->unreadCount($other));
    }
}
