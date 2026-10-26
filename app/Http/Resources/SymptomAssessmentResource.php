<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SymptomAssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'summary' => $this->summary,
            'symptoms' => $this->symptoms,
            'possible_categories' => $this->possible_categories,
            'differential' => $this->differential ?? [],
            'recommended_specialty' => $this->recommended_specialty,
            'referral_needed' => (bool) $this->referral_needed,
            'red_flags' => $this->red_flags,
            'urgency' => $this->urgency->value,
            'recommendation' => $this->recommendation,
            'confidence' => $this->confidence->value,
            'provider' => $this->provider,
            'model' => $this->model,
            'saved_to_journal' => $this->saved_to_journal_at !== null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
