<?php

namespace App\Enums;

enum HealthGoal: string
{
    case GeneralWellness = 'general_wellness';
    case MaintainWeight = 'maintain_weight';
    case LoseWeight = 'lose_weight';
    case GainWeight = 'gain_weight';
    case ImproveFitness = 'improve_fitness';
}

