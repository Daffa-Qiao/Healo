<?php

namespace Database\Factories;

use App\Enums\NutritionSource;
use App\Models\Meal;
use App\Models\MealItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MealItem> */
class MealItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'meal_id' => Meal::factory(),
            'food_name' => fake()->randomElement(['Nasi putih', 'Ayam panggang', 'Sayur bening']),
            'serving_size' => 100,
            'serving_unit' => 'g',
            'calories' => fake()->numberBetween(80, 350),
            'protein_g' => fake()->numberBetween(2, 25),
            'carbohydrates_g' => fake()->numberBetween(5, 50),
            'fat_g' => fake()->numberBetween(1, 15),
            'source' => NutritionSource::Manual,
            'confidence' => null,
        ];
    }
}
