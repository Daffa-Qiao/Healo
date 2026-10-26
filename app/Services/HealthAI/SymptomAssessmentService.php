<?php

namespace App\Services\HealthAI;

use App\Contracts\HealthAIProviderInterface;
use App\Data\SymptomAssessmentResult;
use App\Enums\AIConfidence;
use App\Enums\SymptomUrgency;
use App\Models\HealthConversation;
use App\Models\SymptomAssessment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class SymptomAssessmentService
{
    public function __construct(
        private readonly HealthAIProviderInterface $provider,
        private readonly HealthAIContextService $context,
        private readonly HealthAIResponseParser $parser,
        private readonly EmergencyDetectionService $emergency,
        private readonly MedicalSafetyService $safety,
        private readonly SymptomAssessmentPrompt $prompt,
    ) {}

    public function assess(User $user, string $input, bool $save = false, ?HealthConversation $conversation = null): SymptomAssessmentResult|SymptomAssessment
    {
        $redFlags = $this->emergency->detect($input);

        if ($redFlags !== []) {
            $result = $this->safety->emergencyAssessment($input, $redFlags);
        } else {
            try {
                $result = $this->parser->assessment($this->provider->assess([
                    'system_instructions' => $this->prompt->instructions(app()->getLocale()),
                    'context' => $this->context->forAssessment($user),
                    'user_message' => $input,
                ]));
                $result = $this->applyReferralGate($result);
            } catch (Throwable $exception) {
                Log::warning('Symptom assessment provider response rejected.', [
                    'provider' => class_basename($this->provider),
                    'exception_type' => $exception::class,
                ]);
                $result = $this->safety->fallbackAssessment();
            }
        }

        if (! $save) {
            return $result;
        }

        return $user->symptomAssessments()->create([
            'health_conversation_id' => $conversation?->id,
            'input' => $input,
            ...$result->toArray(),
        ]);
    }

    private function applyReferralGate(SymptomAssessmentResult $result): SymptomAssessmentResult
    {
        $criticalSpecialty = collect(['Sp.PD-KHOM', 'Sp.N', 'Sp.S', 'Sp.PD', 'Sp.JP', 'Sp.BS'])
            ->contains(fn (string $code): bool => str_contains((string) $result->recommendedSpecialty, $code));
        $requiresReferral = $result->referralNeeded
            || $result->confidence === AIConfidence::Low
            || in_array($result->urgency, [SymptomUrgency::Urgent, SymptomUrgency::Emergency], true)
            || ($criticalSpecialty && $result->confidence !== AIConfidence::High);

        if ($requiresReferral === $result->referralNeeded) {
            return $result;
        }

        return new SymptomAssessmentResult(
            summary: $result->summary,
            symptoms: $result->symptoms,
            possibleCategories: $result->possibleCategories,
            redFlags: $result->redFlags,
            urgency: $result->urgency,
            recommendation: $result->recommendation,
            confidence: $result->confidence,
            provider: $result->provider,
            model: $result->model,
            differential: $result->differential,
            recommendedSpecialty: $result->recommendedSpecialty,
            referralNeeded: $requiresReferral,
        );
    }
}
