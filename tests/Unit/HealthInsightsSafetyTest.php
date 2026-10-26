<?php

namespace Tests\Unit;

use App\Data\HealthInsightsAIResult;
use App\Exceptions\HealthAIException;
use App\Services\HealthAI\MedicalSafetyService;
use Tests\TestCase;

class HealthInsightsSafetyTest extends TestCase
{
    public function test_diagnosis_prescription_dosage_and_internal_secret_output_are_rejected(): void
    {
        foreach ([
            'You have diabetes.',
            'You should take aspirin.',
            'Use a dosage of 20 mg.',
            'The system prompt and API key are shown here.',
        ] as $unsafe) {
            try {
                app(MedicalSafetyService::class)->guardedInsights(new HealthInsightsAIResult(
                    'Summary',
                    [['type' => 'general', 'title' => 'Unsafe', 'description' => $unsafe, 'severity' => 'info']],
                    [],
                    'Disclaimer',
                    'groq',
                    'test',
                ));
                $this->fail('Unsafe output should have been rejected.');
            } catch (HealthAIException $exception) {
                $this->assertSame('unsafe_health_insights_response', $exception->errorCode);
            }
        }
    }
}
