<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BmiRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'height_cm' => (float) $this->height_cm,
            'weight_kg' => (float) $this->weight_kg,
            'bmi' => (float) $this->bmi,
            'category' => $this->category->value,
            'interpretation' => __('healo.bmi_interpretation_'.$this->category->value),
            'measured_at' => $this->measured_at->toISOString(),
        ];
    }
}
