<?php

namespace App\Enums;

enum Mood: string
{
    case VeryGood = 'very_good';
    case Good = 'good';
    case Neutral = 'neutral';
    case Low = 'low';
    case VeryLow = 'very_low';
}
