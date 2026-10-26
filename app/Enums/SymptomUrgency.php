<?php

namespace App\Enums;

enum SymptomUrgency: string
{
    case Routine = 'routine';
    case Soon = 'soon';
    case Urgent = 'urgent';
    case Emergency = 'emergency';
}
