<?php

namespace App\Enums;

enum HealthPlanTaskType: string
{
    case NutritionLog = 'nutrition_log';
    case JournalEntry = 'journal_entry';
    case ReviewNutrition = 'review_nutrition';
    case ReviewHealthGoal = 'review_health_goal';
    case ReviewBmi = 'review_bmi';
    case HealthInsight = 'health_insight';
    case WellnessActivity = 'wellness_activity';
}
