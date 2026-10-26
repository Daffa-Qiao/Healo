<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'notifications_enabled' => $this->notifications_enabled,
            'health_plan_enabled' => $this->health_plan_enabled,
            'meal_reminder_enabled' => $this->meal_reminder_enabled,
            'journal_reminder_enabled' => $this->journal_reminder_enabled,
            'health_goal_enabled' => $this->health_goal_enabled,
            'health_insight_enabled' => $this->health_insight_enabled,
            'wellness_enabled' => $this->wellness_enabled,
            'system_enabled' => $this->system_enabled,
            'quiet_hours_start' => $this->quiet_hours_start ? substr($this->quiet_hours_start, 0, 5) : null,
            'quiet_hours_end' => $this->quiet_hours_end ? substr($this->quiet_hours_end, 0, 5) : null,
            'reminder_time' => substr($this->reminder_time, 0, 5),
        ];
    }
}
