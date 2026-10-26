<?php

namespace App\Enums;

enum HealthPlanTaskStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
}
