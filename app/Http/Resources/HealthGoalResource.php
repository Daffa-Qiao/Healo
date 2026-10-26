<?php

namespace App\Http\Resources;

use App\Services\Insights\HealthGoalService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthGoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $progress = app(HealthGoalService::class)->progress($this->resource);

        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'target_value' => (float) $this->target_value,
            'unit' => $this->unit,
            'start_date' => $this->start_date->toDateString(),
            'target_date' => $this->target_date?->toDateString(),
            'status' => $progress->status->value,
            'stored_status' => $this->status->value,
            'progress' => $progress->toArray(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
