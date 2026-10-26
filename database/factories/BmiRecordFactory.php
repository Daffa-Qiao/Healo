<?php

namespace Database\Factories;

use App\Enums\BmiCategory;
use App\Models\BmiRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BmiRecord> */
class BmiRecordFactory extends Factory
{
    public function definition(): array
    {
        $height = fake()->numberBetween(150, 190);
        $weight = fake()->numberBetween(48, 95);
        $bmi = round($weight / (($height / 100) ** 2), 2);

        return [
            'user_id' => User::factory(),
            'height_cm' => $height,
            'weight_kg' => $weight,
            'bmi' => $bmi,
            'category' => match (true) {
                $bmi < 18.5 => BmiCategory::Underweight,
                $bmi < 25 => BmiCategory::Healthy,
                $bmi < 30 => BmiCategory::Overweight,
                default => BmiCategory::Obesity,
            },
            'measured_at' => fake()->dateTimeBetween('-6 months'),
        ];
    }
}
