<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthGoalFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_update_view_and_delete_a_goal(): void
    {
        $user = User::factory()->create();
        $payload = [
            'type' => 'journal_frequency', 'target_value' => 4,
            'start_date' => today()->toDateString(), 'target_date' => today()->addMonth()->toDateString(),
        ];

        $this->actingAs($user)->post('/health-goals', $payload)->assertRedirect('/health-goals');
        $goal = $user->healthGoals()->firstOrFail();
        $this->assertSame('entries/week', $goal->unit);
        $this->assertNull($goal->current_value);

        $this->actingAs($user)->get('/health-goals')->assertOk()->assertSee(__('insights.type_journal_frequency'));
        $this->actingAs($user)->put('/health-goals/'.$goal->id, [
            'target_value' => 5, 'start_date' => today()->toDateString(),
            'target_date' => today()->addMonths(2)->toDateString(), 'status' => 'paused',
        ])->assertRedirect('/health-goals');
        $this->assertSame('5.00', $goal->refresh()->target_value);
        $this->assertSame('paused', $goal->status->value);

        $this->actingAs($user)->delete('/health-goals/'.$goal->id)->assertRedirect('/health-goals');
        $this->assertDatabaseMissing('health_goals', ['id' => $goal->id]);
    }

    public function test_goal_validation_rejects_invalid_type_range_and_dates(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/health-goals', [
            'type' => 'diagnosis', 'target_value' => 30,
            'start_date' => today()->toDateString(),
        ])->assertSessionHasErrors('type');

        $this->actingAs($user)->post('/health-goals', [
            'type' => 'bmi', 'target_value' => 9999,
            'start_date' => today()->addDay()->toDateString(), 'target_date' => today()->toDateString(),
        ])->assertSessionHasErrors(['target_value', 'start_date', 'target_date']);
        $this->assertDatabaseCount('health_goals', 0);
    }

    public function test_health_insights_and_dashboard_render_actual_empty_states(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/health-insights')
            ->assertOk()->assertSee(__('insights.no_bmi'))->assertSee(__('insights.no_goals'));
        $this->actingAs($user)->get('/dashboard')
            ->assertOk()->assertSee(__('insights.weekly_summary'))->assertSee(__('insights.no_goals'));
    }
}
