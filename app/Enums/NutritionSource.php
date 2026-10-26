<?php

namespace App\Enums;

enum NutritionSource: string
{
    case Manual = 'manual';
    case AiEstimate = 'ai_estimate';
    case System = 'system';
}
