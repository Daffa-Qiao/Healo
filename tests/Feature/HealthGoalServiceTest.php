<?php

namespace Tests\Feature;

use App\Enums\HealthGoalStatus;
use App\Models\HealthProfile;
use App\Models\User;
use App\Services\Insights\HealthGoalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthGoalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_progress_uses_live_source_data_and_safe_percentage(): void
    {
        $user = User::factory()->create();
        HealthProfile::factory()->for($user)->create(['weight_kg' => 80]);
        $service = app(HealthGoalService::class);
        $goal = $service->create($user, [
            'type' => 'weight', 'target_value' => 70, 'start_date' => today()->toDateString(),
        ]);
        $user->bmiRecords()->create([
            'height_cm' => 170, 'weight_kg' => 75, 'bmi' => 25.95,
            'category' => 'overweight', 'measured_at' => now(),
        ]);

        $progress = $service->progress($goal);

        $this->assertSame(75.0, $progress->currentValue);
        $this->assertSame(50.0, $progress->percentage);
        $this->assertSame(5.0, $progress->remainingValue);
        $this->assertSame(HealthGoalStatus::Active, $progress->status);
        $this->assertNull($goal->current_value);
    }

    public function test_frequency_progress_is_clamped_and_can_be_derived_completed(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 3) as $index) {
            $user->healthJournals()->create([
                'title' => 'Journal '.$index, 'journal_date' => today()->subDays($index),
            ]);
        }
        $goal = $user->healthGoals()->create([
            'type' => 'journal_frequency', 'target_value' => 2, 'unit' => 'entries/week',
            'start_date' => today()->subWeek(), 'status' => 'active', 'metadata' => ['starting_value' => 0],
        ]);

        $progress = app(HealthGoalService::class)->progress($goal);

        $this->assertSame(100.0, $progress->percentage);
        $this->assertSame(HealthGoalStatus::Completed, $progress->status);
    }
}
