<?php

namespace Database\Factories;

use App\Enums\HealthMessageRole;
use App\Enums\HealthMessageType;
use App\Models\HealthConversation;
use App\Models\HealthMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HealthMessage> */
class HealthMessageFactory extends Factory
{
    protected $model = HealthMessage::class;

    public function definition(): array
    {
        return [
            'health_conversation_id' => HealthConversation::factory(),
            'role' => HealthMessageRole::User,
            'message_type' => HealthMessageType::Chat,
            'content' => fake()->sentence(),
            'metadata' => null,
        ];
    }
}
