<?php

namespace Database\Factories;

use App\Enums\HealthPlanStatus;
use App\Models\HealthPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HealthPlan> */
class HealthPlanFactory extends Factory
{
    protected $model = HealthPlan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_date' => today(),
            'status' => HealthPlanStatus::Active,
            'progress_percentage' => 0,
            'generated_at' => now(),
        ];
    }
}
