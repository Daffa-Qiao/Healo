<?php

namespace App\Enums;

enum HealthGoalType: string
{
    case Weight = 'weight';
    case Bmi = 'bmi';
    case DailyCalories = 'daily_calories';
    case JournalFrequency = 'journal_frequency';
    case NutritionLogging = 'nutrition_logging';
}
