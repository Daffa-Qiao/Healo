<?php

namespace App\Models;

use Database\Factories\NotificationPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    /** @use HasFactory<NotificationPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'notifications_enabled', 'health_plan_enabled', 'meal_reminder_enabled',
        'journal_reminder_enabled', 'health_goal_enabled', 'health_insight_enabled',
        'wellness_enabled', 'system_enabled', 'quiet_hours_start', 'quiet_hours_end',
        'reminder_time',
    ];

    protected function casts(): array
    {
        return [
            'notifications_enabled' => 'boolean',
            'health_plan_enabled' => 'boolean',
            'meal_reminder_enabled' => 'boolean',
            'journal_reminder_enabled' => 'boolean',
            'health_goal_enabled' => 'boolean',
            'health_insight_enabled' => 'boolean',
            'wellness_enabled' => 'boolean',
            'system_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
