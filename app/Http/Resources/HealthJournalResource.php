<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthJournalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'journal_date' => $this->journal_date->toDateString(),
            'mood' => $this->mood?->value,
            'metrics' => [
                'weight_kg' => $this->weight_kg !== null ? (float) $this->weight_kg : null,
                'systolic' => $this->systolic,
                'diastolic' => $this->diastolic,
                'sleep_hours' => $this->sleep_hours !== null ? (float) $this->sleep_hours : null,
                'water_liters' => $this->water_liters !== null ? (float) $this->water_liters : null,
                'exercise_minutes' => $this->exercise_minutes,
            ],
            'entries' => HealthJournalEntryResource::collection($this->whenLoaded('entries')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
