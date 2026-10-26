<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'food_name' => $this->food_name,
            'serving_size' => (float) $this->serving_size,
            'serving_unit' => $this->serving_unit,
            'calories' => (float) $this->calories,
            'protein_g' => (float) $this->protein_g,
            'carbohydrates_g' => (float) $this->carbohydrates_g,
            'fat_g' => (float) $this->fat_g,
            'source' => $this->source->value,
            'confidence' => $this->confidence !== null ? (float) $this->confidence : null,
        ];
    }
}
