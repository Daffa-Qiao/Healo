<?php

namespace App\Enums;

enum NutritionGoal: string
{
    case Maintain = 'maintain';
    case LoseWeight = 'lose_weight';
    case GainWeight = 'gain_weight';
}
