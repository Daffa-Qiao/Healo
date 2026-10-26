<?php

namespace Database\Factories;

use App\Enums\ActivityLevel;
use App\Enums\DietPreference;
use App\Enums\NutritionGoal;
use App\Models\NutritionProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NutritionProfile> */
class NutritionProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'daily_calorie_target' => 2000,
            'activity_level' => ActivityLevel::Moderate,
            'goal' => NutritionGoal::Maintain,
            'diet_preference' => DietPreference::Balanced,
            'food_preferences' => [],
            'target_source' => 'calculated',
            'calculated_at' => now(),
        ];
    }
}
