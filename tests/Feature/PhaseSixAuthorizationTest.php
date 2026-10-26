<?php

namespace Tests\Feature;

use App\Enums\HealthPlanTaskType;
use App\Models\User;
use App\Services\HealthPlan\HealthPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseSixAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_complete_another_users_task(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $task = app(HealthPlanService::class)->generate($owner)->tasks
            ->firstWhere('type', HealthPlanTaskType::WellnessActivity);
        Sanctum::actingAs($intruder);

        $this->postJson('/api/v1/health-plan/tasks/'.$task->id.'/complete')->assertForbidden();
        $this->assertSame('pending', $task->refresh()->status->value);
    }

    public function test_admin_has_no_health_plan_or_task_policy_bypass(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $plan = app(HealthPlanService::class)->generate($owner);
        $task = $plan->tasks->first();
        $this->actingAs($admin);

        $this->assertFalse(Gate::allows('view', $plan));
        $this->assertFalse(Gate::allows('update', $plan));
        $this->assertFalse(Gate::allows('complete', $task));
    }

    public function test_plan_list_is_scoped_to_authenticated_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        app(HealthPlanService::class)->generate($owner);
        Sanctum::actingAs($intruder);

        $this->getJson('/api/v1/health-plan?user_id='.$owner->id)
            ->assertOk()->assertJsonCount(0, 'data');
    }
}
