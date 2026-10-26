<?php

namespace Tests\Feature;

use App\Enums\HealthPlanTaskType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseSixApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_plan_api_requires_sanctum_authentication(): void
    {
        $this->getJson('/api/v1/health-plan')->assertUnauthorized();
        $this->getJson('/api/v1/health-plan/today')->assertUnauthorized();
        $this->postJson('/api/v1/health-plan/generate')->assertUnauthorized();
    }

    public function test_user_can_generate_read_complete_and_uncomplete_plan_through_api(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/health-plan/today')->assertNotFound();
        $response = $this->postJson('/api/v1/health-plan/generate')
            ->assertCreated()->assertJsonPath('data.progress_percentage', 0);
        $task = collect($response->json('data.tasks'))->firstWhere('type', HealthPlanTaskType::WellnessActivity->value);

        $this->getJson('/api/v1/health-plan')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/health-plan/today')->assertOk()
            ->assertJsonStructure(['data' => ['tasks'], 'personalization']);
        $this->postJson('/api/v1/health-plan/tasks/'.$task['id'].'/complete', ['progress' => 100])
            ->assertUnprocessable();
        $this->postJson('/api/v1/health-plan/tasks/'.$task['id'].'/complete')
            ->assertOk()->assertJsonPath('data.progress_percentage', 100);
        $this->postJson('/api/v1/health-plan/tasks/'.$task['id'].'/uncomplete')
            ->assertOk()->assertJsonPath('data.progress_percentage', 0);
    }

    public function test_generate_accepts_today_only(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/v1/health-plan/generate', ['plan_date' => today()->addDay()->toDateString()])
            ->assertUnprocessable()->assertJsonValidationErrors('plan_date');
    }
}
