<?php

namespace App\Enums;

enum DietPreference: string
{
    case Balanced = 'balanced';
    case Vegetarian = 'vegetarian';
    case Vegan = 'vegan';
    case HighProtein = 'high_protein';
}
