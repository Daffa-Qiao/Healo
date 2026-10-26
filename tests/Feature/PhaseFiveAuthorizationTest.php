<?php

namespace Tests\Feature;

use App\Models\HealthGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseFiveAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_read_update_or_delete_another_users_goal(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $goal = HealthGoal::factory()->for($owner)->create();
        Sanctum::actingAs($intruder);

        $this->getJson('/api/v1/health-goals/'.$goal->id)->assertForbidden();
        $this->putJson('/api/v1/health-goals/'.$goal->id, [
            'target_value' => 5, 'start_date' => today()->toDateString(), 'status' => 'active',
        ])->assertForbidden();
        $this->deleteJson('/api/v1/health-goals/'.$goal->id)->assertForbidden();
        $this->assertDatabaseHas('health_goals', ['id' => $goal->id]);
    }

    public function test_admin_has_no_health_goal_policy_bypass(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $goal = HealthGoal::factory()->for($owner)->create();
        $this->actingAs($admin);

        $this->assertFalse(Gate::allows('view', $goal));
        $this->assertFalse(Gate::allows('update', $goal));
        $this->assertFalse(Gate::allows('delete', $goal));
    }

    public function test_health_insight_endpoint_ignores_foreign_user_identifier(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        HealthGoal::factory()->for($owner)->create();
        Sanctum::actingAs($intruder);

        $this->getJson('/api/v1/health-insights?user_id='.$owner->id)
            ->assertOk()->assertJsonCount(0, 'data.overview.goals');
    }
}
