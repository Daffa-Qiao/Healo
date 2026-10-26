<?php

namespace Database\Factories;

use App\Enums\MealType;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Meal> */
class MealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'meal_date' => fake()->dateTimeBetween('-7 days'),
            'meal_type' => fake()->randomElement(MealType::cases()),
            'notes' => null,
            'total_calories' => 0,
        ];
    }
}
