<?php

namespace Tests\Feature;

use App\Enums\HealthPlanTaskStatus;
use App\Enums\HealthPlanTaskType;
use App\Models\HealthJournal;
use App\Models\Meal;
use App\Models\NutritionProfile;
use App\Models\User;
use App\Services\HealthPlan\HealthPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HealthPlanServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_generation_is_deterministic_and_prevents_daily_duplicates(): void
    {
        $user = User::factory()->create();
        NutritionProfile::factory()->for($user)->create();
        $service = app(HealthPlanService::class);

        $first = $service->generate($user);
        $second = $service->generate($user);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, $user->healthPlans()->count());
        $this->assertCount(5, $second->tasks);
        $this->assertSame(0, $second->progress_percentage);
    }

    public function test_data_derived_tasks_follow_real_meal_and_journal_records(): void
    {
        $user = User::factory()->create();
        NutritionProfile::factory()->for($user)->create();
        HealthJournal::factory()->for($user)->create(['journal_date' => today()]);
        Meal::factory()->for($user)->create(['meal_date' => today(), 'meal_type' => 'breakfast']);

        $plan = app(HealthPlanService::class)->generate($user);

        $this->assertSame(
            HealthPlanTaskStatus::Completed,
            $plan->tasks->firstWhere('source_reference', 'meal:breakfast')->status,
        );
        $this->assertSame(
            HealthPlanTaskStatus::Completed,
            $plan->tasks->firstWhere('type', HealthPlanTaskType::JournalEntry)->status,
        );
        $this->assertGreaterThan(0, $plan->progress_percentage);
    }

    public function test_data_derived_task_cannot_be_faked_as_complete(): void
    {
        $user = User::factory()->create();
        NutritionProfile::factory()->for($user)->create();
        $task = app(HealthPlanService::class)->generate($user)->tasks
            ->firstWhere('type', HealthPlanTaskType::NutritionLog);

        $this->expectException(ValidationException::class);
        app(HealthPlanService::class)->complete($task);
    }

    public function test_manual_task_can_be_completed_and_reopened_with_server_progress(): void
    {
        $user = User::factory()->create();
        $service = app(HealthPlanService::class);
        $plan = $service->generate($user);
        $task = $plan->tasks->firstWhere('type', HealthPlanTaskType::WellnessActivity);

        $completed = $service->complete($task);
        $this->assertSame(100, $completed->progress_percentage);
        $this->assertNotNull($completed->completed_at);

        $reopened = $service->uncomplete($task->refresh());
        $this->assertSame(0, $reopened->progress_percentage);
        $this->assertNull($reopened->completed_at);
    }
}
