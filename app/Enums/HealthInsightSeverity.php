<?php

namespace App\Enums;

enum HealthInsightSeverity: string
{
    case Info = 'info';
    case Positive = 'positive';
    case Attention = 'attention';
}
