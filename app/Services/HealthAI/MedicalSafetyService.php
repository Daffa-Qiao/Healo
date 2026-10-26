<?php

namespace App\Services\HealthAI;

use App\Data\HealthAIResponse;
use App\Data\HealthInsightsAIResult;
use App\Data\HealthPlanPersonalization;
use App\Data\SymptomAssessmentResult;
use App\Enums\AIConfidence;
use App\Enums\SymptomUrgency;
use App\Exceptions\HealthAIException;

class MedicalSafetyService
{
    public function __construct(private readonly EmergencyDetectionService $emergency) {}

    public function guardedAssistantResponse(string $message, ?HealthAIResponse $response = null): ?HealthAIResponse
    {
        $flags = $this->emergency->detect($message);
        if ($flags !== []) {
            return new HealthAIResponse(
                __('health_ai.emergency_chat_response'),
                'local_demo',
                'emergency_rules_v1',
                AIConfidence::High,
                ['red_flags' => $flags, 'urgency' => SymptomUrgency::Emergency->value],
            );
        }

        $lower = mb_strtolower($message);
        if ($this->contains($lower, ['dosis', 'dosage', 'resep', 'prescribe', 'obat apa', 'what medicine', 'berapa mg', 'berapa tablet', 'how many mg'])) {
            return new HealthAIResponse(
                __('health_ai.medication_boundary_response'),
                'local_demo',
                'medical_safety_v1',
                AIConfidence::High,
                ['safety_boundary' => 'medication'],
            );
        }

        if ($this->contains($lower, ['diagnosis', 'diagnose me', 'penyakit apa', 'ignore all previous', 'abaikan semua instruksi'])) {
            return new HealthAIResponse(
                __('health_ai.diagnosis_boundary_response'),
                'local_demo',
                'medical_safety_v1',
                AIConfidence::High,
                ['safety_boundary' => 'diagnosis_or_prompt_injection'],
            );
        }

        if ($response && preg_match('/\b(you have|you are diagnosed with|anda pasti|anda menderita)\b/i', $response->content)) {
            return new HealthAIResponse(
                __('health_ai.unsafe_response_replaced'),
                $response->provider,
                $response->model,
                AIConfidence::Low,
                ['safety_boundary' => 'unsafe_provider_output'],
            );
        }

        return $response;
    }

    /** @param list<string> $redFlags */
    public function emergencyAssessment(string $input, array $redFlags): SymptomAssessmentResult
    {
        return new SymptomAssessmentResult(
            summary: __('health_ai.emergency_summary'),
            symptoms: [['name' => mb_substr($input, 0, 120), 'severity' => 'severe']],
            possibleCategories: [],
            redFlags: $redFlags,
            urgency: SymptomUrgency::Emergency,
            recommendation: __('health_ai.emergency_recommendation'),
            confidence: AIConfidence::High,
            provider: 'local_demo',
            model: 'emergency_rules_v1',
        );
    }

    public function fallbackAssessment(): SymptomAssessmentResult
    {
        return new SymptomAssessmentResult(
            summary: __('health_ai.assessment_unavailable'),
            symptoms: [],
            possibleCategories: [],
            redFlags: [],
            urgency: SymptomUrgency::Soon,
            recommendation: __('health_ai.fallback_recommendation'),
            confidence: AIConfidence::Low,
            provider: 'fallback',
            model: 'safety_fallback_v1',
        );
    }

    public function guardedInsights(HealthInsightsAIResult $result): HealthInsightsAIResult
    {
        $text = collect([$result->summary, $result->disclaimer])
            ->merge(collect($result->insights)->pluck('description'))
            ->merge(collect($result->recommendations)->pluck('text'))
            ->implode(' ');

        if (preg_match('/\b(you have|you are diagnosed with|anda pasti|anda menderita|take \d+\s?mg|minum \d+\s?mg|you should take|anda harus minum|prescription|resepkan|dosage|dosis|api[_ ]?key|system prompt|internal architecture)\b/i', $text)) {
            throw new HealthAIException('unsafe_health_insights_response');
        }

        return new HealthInsightsAIResult(
            $result->summary,
            $result->insights,
            $result->recommendations,
            __('insights.ai_disclaimer'),
            $result->provider,
            $result->model,
            $result->fallback,
        );
    }

    public function guardedWellness(HealthPlanPersonalization $result): HealthPlanPersonalization
    {
        $text = implode(' ', [$result->summary, $result->suggestion, $result->motivation, $result->disclaimer]);
        if (preg_match('/\b(you have|you are diagnosed with|anda pasti|anda menderita|take \d+\s?mg|minum \d+\s?mg|you should take|anda harus minum|prescription|resepkan|dosage|dosis|api[_ ]?key|system prompt|internal architecture)\b/i', $text)) {
            throw new HealthAIException('unsafe_wellness_response');
        }

        return new HealthPlanPersonalization(
            $result->summary,
            $result->suggestion,
            $result->motivation,
            __('wellness.ai_disclaimer'),
            $result->provider,
            $result->model,
            $result->fallback,
        );
    }

    /** @param list<string> $needles */
    private function contains(string $text, array $needles): bool
    {
        return collect($needles)->contains(fn (string $needle): bool => str_contains($text, $needle));
    }
}
