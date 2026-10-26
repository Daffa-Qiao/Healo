<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Events\NotificationCreated;
use App\Models\User;
use App\Services\Notification\NotificationPreferenceService;
use App\Services\Notification\NotificationService;
use Database\Seeders\DemoNotificationSeeder;
use Database\Seeders\DevelopmentAccountSeeder;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PhaseSevenBroadcastAndSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_notification_broadcasts_only_to_the_owner_channel(): void
    {
        Event::fake([NotificationCreated::class]);
        $user = User::factory()->create();

        app(NotificationService::class)->create(
            $user,
            NotificationType::System,
            'Sistem',
            'Pembaruan tersedia.',
            referenceKey: 'broadcast-test',
        );

        Event::assertDispatched(NotificationCreated::class, function (NotificationCreated $event) use ($user): bool {
            $channel = $event->broadcastOn()[0];

            return $channel instanceof PrivateChannel
                && $channel->name === 'private-users.'.$user->id.'.notifications'
                && ! array_key_exists('data', $event->broadcastWith()['notification']);
        });
    }

    public function test_quiet_hours_store_notification_without_active_broadcast(): void
    {
        Event::fake([NotificationCreated::class]);
        $this->travelTo(now()->setTime(23, 0));
        $user = User::factory()->create();
        app(NotificationPreferenceService::class)->update($user, ['quiet_hours_start' => '22:00', 'quiet_hours_end' => '07:00']);

        app(NotificationService::class)->create($user, NotificationType::Wellness, 'Wellness', 'Pesan.', referenceKey: 'quiet-test');

        Event::assertNotDispatched(NotificationCreated::class);
    }

    public function test_deferred_notification_is_broadcast_once_after_quiet_hours(): void
    {
        Event::fake([NotificationCreated::class]);
        $this->travelTo(now()->setTime(23, 0));
        $user = User::factory()->create();
        app(NotificationPreferenceService::class)->update($user, ['quiet_hours_start' => '22:00', 'quiet_hours_end' => '07:00']);
        $notification = app(NotificationService::class)->create($user, NotificationType::Wellness, 'Wellness', 'Pesan.', referenceKey: 'deferred-test');
        $this->travelTo(now()->addDay()->setTime(7, 1));

        $this->assertSame(1, app(NotificationService::class)->deliverDue());
        $this->assertSame(0, app(NotificationService::class)->deliverDue());
        $this->assertNotNull($notification?->refresh()->delivered_at);
        Event::assertDispatchedTimes(NotificationCreated::class, 1);
    }

    public function test_demo_notification_seeder_is_idempotent_with_three_unread_and_two_read(): void
    {
        $this->seed(DevelopmentAccountSeeder::class);
        $this->seed(DemoNotificationSeeder::class);
        $this->seed(DemoNotificationSeeder::class);
        $user = User::query()->where('email', 'demo@healo.test')->firstOrFail();

        $this->assertSame(5, $user->userNotifications()->count());
        $this->assertSame(3, $user->userNotifications()->unread()->count());
        $this->assertSame(2, $user->userNotifications()->whereNotNull('read_at')->count());
    }
}
