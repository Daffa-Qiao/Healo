<?php

namespace App\Enums;

enum NotificationType: string
{
    case HealthPlan = 'health_plan';
    case MealReminder = 'meal_reminder';
    case JournalReminder = 'journal_reminder';
    case HealthGoal = 'health_goal';
    case HealthInsight = 'health_insight';
    case Wellness = 'wellness';
    case System = 'system';

    public function preferenceColumn(): string
    {
        return match ($this) {
            self::HealthPlan => 'health_plan_enabled',
            self::MealReminder => 'meal_reminder_enabled',
            self::JournalReminder => 'journal_reminder_enabled',
            self::HealthGoal => 'health_goal_enabled',
            self::HealthInsight => 'health_insight_enabled',
            self::Wellness => 'wellness_enabled',
            self::System => 'system_enabled',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::System => 'system',
            self::Wellness, self::HealthPlan => 'wellness',
            default => 'health',
        };
    }
}
