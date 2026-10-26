<?php

namespace App\Enums;

enum HealthMessageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';
    case System = 'system';
}
