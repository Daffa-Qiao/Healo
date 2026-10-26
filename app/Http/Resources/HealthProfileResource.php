<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'height_cm' => $this->height_cm !== null ? (float) $this->height_cm : null,
            'weight_kg' => $this->weight_kg !== null ? (float) $this->weight_kg : null,
            'birth_date' => $this->birth_date?->toDateString(),
            'gender' => $this->gender?->value,
            'activity_level' => $this->activity_level?->value,
            'health_goal' => $this->health_goal?->value,
            'health_conditions' => $this->health_conditions ?? [],
            'allergies' => $this->allergies ?? [],
            'consented_at' => $this->consented_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
