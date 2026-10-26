<?php

namespace App\Enums;

enum HealthPlanStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
}
