<?php

namespace App\Enums;

enum BmiCategory: string
{
    case Underweight = 'underweight';
    case Healthy = 'healthy';
    case Overweight = 'overweight';
    case Obesity = 'obesity';
}
