<?php

namespace App\Services\Notification;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Arr;

class NotificationPreferenceService
{
    private const FIELDS = [
        'notifications_enabled', 'health_plan_enabled', 'meal_reminder_enabled',
        'journal_reminder_enabled', 'health_goal_enabled', 'health_insight_enabled',
        'wellness_enabled', 'system_enabled', 'quiet_hours_start', 'quiet_hours_end',
        'reminder_time',
    ];

    public function forUser(User $user): NotificationPreference
    {
        return $user->notificationPreference()->firstOrCreate()->refresh();
    }

    /** @param array<string, mixed> $data */
    public function update(User $user, array $data): NotificationPreference
    {
        $preference = $this->forUser($user);
        $preference->update(Arr::only($data, self::FIELDS));

        return $preference->refresh();
    }
}
