<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthyMenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'meal_type' => $this->meal_type->value,
            'diet_preference' => $this->diet_preference->value,
            'calories' => (float) $this->calories,
            'protein_g' => (float) $this->protein_g,
            'carbohydrates_g' => (float) $this->carbohydrates_g,
            'fat_g' => (float) $this->fat_g,
            'ingredients' => $this->ingredients,
            'preparation' => $this->preparation,
            'source' => $this->source,
            'attribution' => $this->attribution,
        ];
    }
}
