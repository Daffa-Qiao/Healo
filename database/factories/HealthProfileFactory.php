<?php

namespace Database\Factories;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\HealthGoal;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HealthProfile> */
class HealthProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'height_cm' => fake()->numberBetween(145, 190),
            'weight_kg' => fake()->numberBetween(45, 95),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'gender' => fake()->randomElement(Gender::cases()),
            'activity_level' => fake()->randomElement(ActivityLevel::cases()),
            'health_goal' => fake()->randomElement(HealthGoal::cases()),
            'health_conditions' => [],
            'allergies' => [],
            'consented_at' => now(),
        ];
    }
}
