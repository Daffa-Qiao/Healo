<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodAnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'result' => $this->result,
            'confidence' => $this->confidence !== null ? (float) $this->confidence : null,
            'low_confidence' => $this->confidence !== null
                && (float) $this->confidence < (float) config('nutrition.low_confidence_threshold', 0.6),
            'provider' => $this->provider,
            'has_private_image' => $this->image_path !== null,
            'analyzed_at' => $this->analyzed_at?->toISOString(),
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
        ];
    }
}
