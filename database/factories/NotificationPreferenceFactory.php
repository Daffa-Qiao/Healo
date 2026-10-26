<?php

namespace Database\Factories;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NotificationPreference> */
class NotificationPreferenceFactory extends Factory
{
    protected $model = NotificationPreference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notifications_enabled' => true,
            'health_plan_enabled' => true,
            'meal_reminder_enabled' => true,
            'journal_reminder_enabled' => true,
            'health_goal_enabled' => true,
            'health_insight_enabled' => true,
            'wellness_enabled' => true,
            'system_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
            'reminder_time' => '08:00',
        ];
    }
}
