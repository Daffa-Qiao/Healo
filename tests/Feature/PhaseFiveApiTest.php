<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseFiveApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_insights_and_goal_api_require_authentication(): void
    {
        $this->getJson('/api/v1/health-insights')->assertUnauthorized();
        $this->getJson('/api/v1/health-goals')->assertUnauthorized();
        $this->postJson('/api/v1/health-goals', [])->assertUnauthorized();
    }

    public function test_health_insights_api_returns_overview_trends_and_weekly_structure(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/health-insights?period=30')->assertOk()
            ->assertJsonPath('data.overview.period_days', 30)
            ->assertJsonStructure(['data' => ['overview' => ['bmi', 'nutrition', 'journal', 'ai', 'goals'], 'personalized']]);
        $this->getJson('/api/v1/health-insights/trends?period=90')->assertOk()
            ->assertJsonPath('data.period_days', 90)->assertJsonCount(90, 'data.nutrition.labels');
        $this->getJson('/api/v1/health-insights/weekly')->assertOk()
            ->assertJsonStructure(['data' => ['health', 'nutrition', 'journal', 'ai', 'goals', 'insights']]);
        $this->getJson('/api/v1/health-insights/trends?period=8')->assertUnprocessable();
    }

    public function test_user_can_manage_goal_api_and_client_user_id_is_ignored(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/health-goals', [
            'user_id' => $other->id,
            'type' => 'nutrition_logging', 'target_value' => 12,
            'start_date' => today()->toDateString(), 'target_date' => today()->addMonth()->toDateString(),
        ])->assertCreated()->assertJsonPath('data.type', 'nutrition_logging');
        $id = $response->json('data.id');
        $this->assertSame($user->id, $user->healthGoals()->findOrFail($id)->user_id);

        $this->getJson('/api/v1/health-goals')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/health-goals/'.$id)->assertOk()->assertJsonPath('data.progress.unit', 'meals/week');
        $this->putJson('/api/v1/health-goals/'.$id, [
            'target_value' => 15, 'start_date' => today()->toDateString(),
            'target_date' => today()->addMonths(2)->toDateString(), 'status' => 'active',
        ])->assertOk()->assertJsonPath('data.target_value', 15);
        $this->deleteJson('/api/v1/health-goals/'.$id)->assertNoContent();
    }

    public function test_ai_insight_limit_is_per_user(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        Sanctum::actingAs($first);
        foreach (range(1, 10) as $attempt) {
            $this->getJson('/api/v1/health-insights')->assertOk();
        }
        $this->getJson('/api/v1/health-insights')->assertTooManyRequests();

        Sanctum::actingAs($second);
        $this->getJson('/api/v1/health-insights')->assertOk();
    }
}
