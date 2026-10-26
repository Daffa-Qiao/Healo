<?php

namespace App\Http\Resources;

use App\Enums\HealthPlanTaskStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tasks = $this->whenLoaded('tasks');
        $total = $this->relationLoaded('tasks') ? $this->tasks->count() : 0;
        $completed = $this->relationLoaded('tasks')
            ? $this->tasks->where('status', HealthPlanTaskStatus::Completed)->count()
            : 0;

        return [
            'id' => $this->id,
            'plan_date' => $this->plan_date->toDateString(),
            'status' => $this->status->value,
            'progress_percentage' => $this->progress_percentage,
            'completed_tasks' => $completed,
            'remaining_tasks' => max(0, $total - $completed),
            'generated_at' => $this->generated_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'tasks' => HealthPlanTaskResource::collection($tasks),
        ];
    }
}
