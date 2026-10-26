<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NutritionDailySummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->summary_date->toDateString(),
            'daily_target' => $this->calorie_target,
            'consumed' => (float) $this->calories_consumed,
            'remaining' => (float) $this->calories_remaining,
            'target_exceeded' => (float) $this->calories_remaining < 0,
            'meals_logged' => $this->meals_logged,
        ];
    }
}
