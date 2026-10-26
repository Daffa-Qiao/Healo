<?php

namespace Database\Factories;

use App\Enums\HealthGoalStatus;
use App\Enums\HealthGoalType;
use App\Models\HealthGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HealthGoal> */
class HealthGoalFactory extends Factory
{
    protected $model = HealthGoal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => HealthGoalType::JournalFrequency,
            'target_value' => 4,
            'current_value' => null,
            'unit' => 'entries/week',
            'start_date' => today(),
            'target_date' => today()->addMonth(),
            'status' => HealthGoalStatus::Active,
            'metadata' => ['starting_value' => 0],
        ];
    }
}
