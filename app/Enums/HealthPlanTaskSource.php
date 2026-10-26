<?php

namespace App\Enums;

enum HealthPlanTaskSource: string
{
    case Nutrition = 'nutrition';
    case Journal = 'journal';
    case Bmi = 'bmi';
    case HealthGoal = 'health_goal';
    case Wellness = 'wellness';
    case Insight = 'insight';
}
