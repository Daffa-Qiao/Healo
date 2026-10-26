<?php

namespace Tests\Feature;

use App\Models\BmiRecord;
use App\Models\HealthGoal;
use App\Models\HealthJournal;
use App\Models\Meal;
use App\Models\NutritionProfile;
use App\Models\User;
use App\Services\Insights\DashboardHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_actual_owner_scoped_health_data(): void
    {
        $user = User::factory()->create(['name' => 'Andi Pratama']);
        NutritionProfile::factory()->for($user)->create(['daily_calorie_target' => 1800]);
        BmiRecord::factory()->for($user)->create([
            'bmi' => 22.8, 'weight_kg' => 68.5, 'category' => 'healthy', 'measured_at' => now(),
        ]);
        Meal::factory()->for($user)->create([
            'meal_date' => today(), 'meal_type' => 'breakfast', 'total_calories' => 1420,
        ]);
        HealthJournal::factory()->for($user)->create(['journal_date' => today(), 'mood' => 'good']);
        HealthGoal::factory()->for($user)->create(['type' => 'weight', 'target_value' => 65, 'status' => 'active']);

        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertSee('Andi')->assertSee('22.8')->assertSee('1,420')
            ->assertSee(__('dashboard.active_goals'))->assertSee(__('insights.type_weight'))
            ->assertSee('dashboard-bmi-chart', false);
    }

    public function test_dashboard_does_not_expose_another_users_health_data(): void
    {
        $viewer = User::factory()->create();
        $other = User::factory()->create();
        BmiRecord::factory()->for($other)->create(['bmi' => 39.9, 'measured_at' => now()]);
        HealthGoal::factory()->for($other)->create(['type' => 'daily_calories', 'target_value' => 3333]);

        $this->actingAs($viewer)->get('/dashboard')->assertOk()
            ->assertDontSee('39.9')->assertDontSee('3,333');
    }

    public function test_dashboard_uses_forwarded_https_scheme_behind_a_reverse_proxy(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withServerVariables([
                'HTTP_HOST' => 'healo-tunnel.test',
                'REMOTE_ADDR' => '10.0.0.10',
                'HTTP_X_FORWARDED_HOST' => 'healo-tunnel.test',
                'HTTP_X_FORWARDED_PORT' => '443',
                'HTTP_X_FORWARDED_PROTO' => 'https',
            ])
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertOk()
            ->assertSee('https://healo-tunnel.test/build/assets/', false)
            ->assertSee('action="https://healo-tunnel.test/locale"', false)
            ->assertSee('action="https://healo-tunnel.test/logout"', false)
            ->assertDontSee('http://healo-tunnel.test', false);
    }

    public function test_weekly_summary_compares_only_the_users_current_and_previous_week(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Meal::factory()->for($user)->create(['meal_date' => today(), 'total_calories' => 1200]);
        Meal::factory()->for($user)->create(['meal_date' => today()->subDays(7), 'total_calories' => 1000]);
        Meal::factory()->for($other)->create(['meal_date' => today(), 'total_calories' => 9000]);

        $weekly = app(DashboardHealthService::class)->forUser($user)['weekly'];

        $this->assertSame(1200.0, $weekly['average_calories']);
        $this->assertSame(20.0, $weekly['calorie_change_percentage']);
    }
}
