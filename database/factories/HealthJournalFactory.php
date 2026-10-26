<?php

namespace Database\Factories;

use App\Enums\Mood;
use App\Models\HealthJournal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HealthJournal> */
class HealthJournalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['Catatan pagi', 'Setelah olahraga', 'Kondisi hari ini']),
            'journal_date' => fake()->dateTimeBetween('-30 days'),
            'mood' => fake()->randomElement(Mood::cases()),
            'weight_kg' => fake()->optional()->randomFloat(1, 45, 100),
            'systolic' => fake()->optional()->numberBetween(100, 145),
            'diastolic' => fake()->optional()->numberBetween(65, 95),
            'sleep_hours' => fake()->optional()->randomFloat(1, 5, 9),
            'water_liters' => fake()->optional()->randomFloat(1, 1, 4),
            'exercise_minutes' => fake()->optional()->numberBetween(0, 90),
        ];
    }
}
