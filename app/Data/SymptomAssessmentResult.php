<?php

namespace App\Data;

use App\Enums\AIConfidence;
use App\Enums\SymptomUrgency;

final readonly class SymptomAssessmentResult
{
    /**
     * @param  list<array{name: string, severity: string}>  $symptoms
     * @param  list<string>  $possibleCategories
     * @param  list<string>  $redFlags
     * @param  list<array{rank: int, condition: string, specialty: string, probability: float|int}>  $differential
     */
    public function __construct(
        public string $summary,
        public array $symptoms,
        public array $possibleCategories,
        public array $redFlags,
        public SymptomUrgency $urgency,
        public string $recommendation,
        public AIConfidence $confidence,
        public string $provider,
        public string $model,
        public array $differential = [],
        public ?string $recommendedSpecialty = null,
        public bool $referralNeeded = false,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
            'symptoms' => $this->symptoms,
            'possible_categories' => $this->possibleCategories,
            'differential' => $this->differential,
            'recommended_specialty' => $this->recommendedSpecialty,
            'referral_needed' => $this->referralNeeded,
            'red_flags' => $this->redFlags,
            'urgency' => $this->urgency->value,
            'recommendation' => $this->recommendation,
            'confidence' => $this->confidence->value,
            'provider' => $this->provider,
            'model' => $this->model,
        ];
    }
}
