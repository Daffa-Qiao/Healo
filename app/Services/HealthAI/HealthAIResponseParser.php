<?php

namespace App\Services\HealthAI;

use App\Data\HealthAIResponse;
use App\Data\SymptomAssessmentResult;
use App\Enums\AIConfidence;
use App\Enums\SymptomUrgency;
use App\Exceptions\HealthAIException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HealthAIResponseParser
{
    /** @param array<string, mixed> $payload */
    public function assistant(array $payload): HealthAIResponse
    {
        $validated = Validator::make($payload, [
            'content' => ['required', 'string', 'max:8000'],
            'provider' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:120'],
            'confidence' => ['required', Rule::enum(AIConfidence::class)],
            'metadata' => ['sometimes', 'array'],
        ])->validate();

        return new HealthAIResponse(
            content: $validated['content'],
            provider: $validated['provider'],
            model: $validated['model'],
            confidence: AIConfidence::from($validated['confidence']),
            metadata: $validated['metadata'] ?? [],
        );
    }

    /** @param array<string, mixed> $payload */
    public function assessment(array $payload): SymptomAssessmentResult
    {
        try {
            $validated = Validator::make($payload, [
                'summary' => ['required', 'string', 'max:3000'],
                'symptoms' => ['required', 'array', 'max:20'],
                'symptoms.*.name' => ['required', 'string', 'max:120'],
                'symptoms.*.severity' => ['required', Rule::in(['mild', 'moderate', 'severe'])],
                'possible_categories' => ['present', 'array', 'max:10'],
                'possible_categories.*' => ['string', 'max:120'],
                'differential' => ['present', 'array', 'min:1', 'max:3'],
                'differential.*.rank' => ['required', 'integer', 'between:1,3'],
                'differential.*.condition' => ['required', 'string', 'max:160'],
                'differential.*.specialty' => ['required', 'string', 'max:120'],
                'differential.*.probability' => ['required', 'numeric', 'between:0,0.99'],
                'recommended_specialty' => ['required', 'string', 'max:120'],
                'referral_needed' => ['required', 'boolean'],
                'red_flags' => ['present', 'array', 'max:10'],
                'red_flags.*' => ['string', 'max:120'],
                'urgency' => ['required', Rule::enum(SymptomUrgency::class)],
                'recommendation' => ['required', 'string', 'max:5000'],
                'confidence' => ['required', Rule::enum(AIConfidence::class)],
                'provider' => ['required', 'string', 'max:80'],
                'model' => ['required', 'string', 'max:120'],
            ])->validate();
        } catch (\Throwable $exception) {
            throw new HealthAIException('invalid_ai_response', $exception);
        }

        return new SymptomAssessmentResult(
            summary: $validated['summary'],
            symptoms: $validated['symptoms'],
            possibleCategories: array_values($validated['possible_categories']),
            redFlags: array_values($validated['red_flags']),
            urgency: SymptomUrgency::from($validated['urgency']),
            recommendation: $validated['recommendation'],
            confidence: AIConfidence::from($validated['confidence']),
            provider: $validated['provider'],
            model: $validated['model'],
            differential: array_values($validated['differential']),
            recommendedSpecialty: $validated['recommended_specialty'],
            referralNeeded: (bool) $validated['referral_needed'],
        );
    }
}
