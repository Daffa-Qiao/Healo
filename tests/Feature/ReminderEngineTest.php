<?php

namespace Tests\Feature;

use App\Enums\HealthPlanTaskStatus;
use App\Enums\NotificationType;
use App\Jobs\SendWellnessReminderJob;
use App\Models\HealthJournal;
use App\Models\HealthPlan;
use App\Models\HealthPlanTask;
use App\Models\Meal;
use App\Models\User;
use App\Services\Notification\ReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReminderEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminders_are_derived_from_the_users_actual_daily_data(): void
    {
        $user = User::factory()->create();
        HealthJournal::factory()->for($user)->create(['journal_date' => today()]);
        Meal::factory()->for($user)->create(['meal_date' => today()]);
        $plan = HealthPlan::factory()->for($user)->create(['plan_date' => today()]);
        HealthPlanTask::factory()->for($plan)->create([
            'status' => HealthPlanTaskStatus::Pending,
            'source_reference' => 'wellness-1',
        ]);
        HealthPlanTask::factory()->for($plan)->create([
            'status' => HealthPlanTaskStatus::Pending,
            'source_reference' => 'wellness-2',
        ]);

        app(ReminderService::class)->generateFor($user, today());

        $types = $user->userNotifications()->pluck('type')->map(fn (NotificationType $type) => $type->value)->all();
        $this->assertContains(NotificationType::HealthPlan->value, $types);
        $this->assertContains(NotificationType::Wellness->value, $types);
        $this->assertNotContains(NotificationType::JournalReminder->value, $types);
        $this->assertNotContains(NotificationType::MealReminder->value, $types);
        $this->assertStringContainsString('2 aktivitas', $user->userNotifications()->where('type', NotificationType::HealthPlan)->firstOrFail()->body);
    }

    public function test_reminder_generation_is_idempotent_when_a_job_is_retried(): void
    {
        $user = User::factory()->create();
        $job = new SendWellnessReminderJob($user->id, today()->toDateString());

        $job->handle(app(ReminderService::class));
        $firstCount = $user->userNotifications()->count();
        $job->handle(app(ReminderService::class));

        $this->assertGreaterThan(0, $firstCount);
        $this->assertSame($firstCount, $user->userNotifications()->count());
    }

    public function test_reminder_command_queues_one_job_per_eligible_user(): void
    {
        Queue::fake();
        User::factory()->count(2)->create();

        $this->artisan('healo:send-reminders')->assertSuccessful();

        Queue::assertPushed(SendWellnessReminderJob::class, 2);
    }
}
