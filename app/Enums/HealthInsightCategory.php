<?php

namespace App\Enums;

enum HealthInsightCategory: string
{
    case General = 'general';
    case Bmi = 'bmi';
    case Nutrition = 'nutrition';
    case Journal = 'journal';
    case Goals = 'goals';
    case Activity = 'activity';
}
