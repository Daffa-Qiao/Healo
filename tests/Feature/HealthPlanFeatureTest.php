<?php

namespace Tests\Feature;

use App\Enums\HealthPlanTaskType;
use App\Models\User;
use App\Services\HealthPlan\HealthPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthPlanFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_plan_page_requires_authentication_and_has_empty_state(): void
    {
        $this->get('/health-plan')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())
            ->get('/health-plan')->assertOk()->assertSee(__('wellness.empty_title'));
    }

    public function test_user_can_generate_plan_and_manage_manual_task(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/health-plan/generate', [
            'progress' => 100,
        ])->assertSessionHasErrors('progress');

        $this->actingAs($user)->post('/health-plan/generate')->assertRedirect('/health-plan');
        $plan = $user->healthPlans()->with('tasks')->firstOrFail();
        $task = $plan->tasks->firstWhere('type', HealthPlanTaskType::WellnessActivity);

        $this->post(route('health-plan.tasks.complete', $task), ['progress' => 99])
            ->assertSessionHasErrors('progress');
        $this->post(route('health-plan.tasks.complete', $task))->assertRedirect();
        $this->assertSame('completed', $task->refresh()->status->value);
        $this->post(route('health-plan.tasks.uncomplete', $task))->assertRedirect();
        $this->assertSame('pending', $task->refresh()->status->value);
    }

    public function test_dashboard_shows_existing_daily_plan_without_generating_one(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee(__('wellness.dashboard_empty'));
        app(HealthPlanService::class)->generate($user);

        $this->get('/dashboard')->assertOk()->assertSee('0%')->assertSee(__('wellness.plan_task_wellness'));
    }
}
