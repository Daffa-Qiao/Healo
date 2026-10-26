<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NutritionProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'daily_calorie_target' => $this->daily_calorie_target,
            'activity_level' => $this->activity_level->value,
            'goal' => $this->goal->value,
            'diet_preference' => $this->diet_preference->value,
            'food_preferences' => $this->food_preferences ?? [],
            'target_source' => $this->target_source,
            'calculated_at' => $this->calculated_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
