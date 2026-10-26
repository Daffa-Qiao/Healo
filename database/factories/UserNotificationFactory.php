<?php

namespace Database\Factories;

use App\Enums\NotificationType;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserNotification> */
class UserNotificationFactory extends Factory
{
    protected $model = UserNotification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => NotificationType::Wellness,
            'title' => 'Pengingat wellness',
            'body' => 'Satu langkah kecil dapat mendukung kebiasaan sehatmu.',
            'data' => [],
            'action_url' => '/health-plan',
            'deduplication_key' => 'factory-'.fake()->uuid(),
            'reference_date' => today(),
            'available_at' => null,
            'delivered_at' => now(),
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }
}
