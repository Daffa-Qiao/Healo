<?php

namespace App\Enums;

enum FoodAnalysisStatus: string
{
    case Processing = 'processing';
    case Review = 'review';
    case Failed = 'failed';
    case Confirmed = 'confirmed';
}
