<?php

namespace App\Enums;

enum HealthPlanTaskCompletionMode: string
{
    case Data = 'data';
    case Manual = 'manual';
}
