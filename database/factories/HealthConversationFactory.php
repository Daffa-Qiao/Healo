<?php

namespace Database\Factories;

use App\Models\HealthConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HealthConversation> */
class HealthConversationFactory extends Factory
{
    protected $model = HealthConversation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'last_message_at' => now(),
        ];
    }
}
