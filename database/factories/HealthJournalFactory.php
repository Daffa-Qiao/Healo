<?php

namespace Database\Factories;

use App\Enums\Mood;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\HealthJournal> */
class HealthJournalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['Catatan pagi', 'Setelah olahraga', 'Kondisi hari ini']),
            'journal_date' => fake()->dateTimeBetween('-30 days'),
            'mood' => fake()->randomElement(Mood::cases()),
        ];
    }
}
