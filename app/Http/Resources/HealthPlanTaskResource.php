<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthPlanTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'title' => __($this->title),
            'description' => $this->description ? __($this->description) : null,
            'status' => $this->status->value,
            'priority' => $this->priority,
            'source' => $this->source->value,
            'completion_mode' => $this->completion_mode->value,
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
