<?php

namespace Tests\Fakes;

use App\Contracts\HealthAIProviderInterface;
use RuntimeException;

class FakeHealthAIProvider implements HealthAIProviderInterface
{
    /** @var array<string, mixed>|null */
    public ?array $lastWellnessRequest = null;

    /** @var array<string, mixed>|null */
    public ?array $lastAssessmentRequest = null;

    /** @param array<string, mixed>|null $response @param array<string, mixed>|null $assessment */
    public function __construct(
        public ?array $response = null,
        public ?array $assessment = null,
        public ?array $insightsResponse = null,
        public ?array $wellnessResponse = null,
        public bool $fail = false,
    ) {}

    public function respond(array $request): array
    {
        if ($this->fail) {
            throw new RuntimeException('Sensitive fake provider failure');
        }

        return $this->response ?? [
            'content' => 'This may be related to a general health concern. Consider medical advice if it worsens.',
            'provider' => 'fake',
            'model' => 'fake-v1',
            'confidence' => 'low',
            'metadata' => [],
        ];
    }

    public function assess(array $request): array
    {
        $this->lastAssessmentRequest = $request;

        if ($this->fail) {
            throw new RuntimeException('Sensitive fake provider failure');
        }

        return $this->assessment ?? [
            'summary' => 'Symptoms require general monitoring.',
            'symptoms' => [['name' => 'headache', 'severity' => 'mild']],
            'possible_categories' => ['hydration-related symptoms'],
            'differential' => [[
                'rank' => 1,
                'condition' => 'hydration-related symptoms',
                'specialty' => 'General Practitioner',
                'probability' => 0.55,
            ]],
            'recommended_specialty' => 'General Practitioner',
            'referral_needed' => false,
            'red_flags' => [],
            'urgency' => 'routine',
            'recommendation' => 'Monitor symptoms and seek care if they worsen.',
            'confidence' => 'low',
            'provider' => 'fake',
            'model' => 'fake-v1',
        ];
    }

    public function insights(array $request): array
    {
        if ($this->fail) {
            throw new RuntimeException('Sensitive fake provider failure');
        }

        return $this->insightsResponse ?? [
            'summary' => 'Your recorded data provides a limited weekly overview.',
            'insights' => [[
                'type' => 'nutrition', 'title' => 'Nutrition records',
                'description' => 'Your data shows meals recorded this week.', 'severity' => 'info',
            ]],
            'recommendations' => [[
                'category' => 'nutrition', 'text' => 'You may consider logging meals consistently.',
            ]],
            'disclaimer' => 'This information is based on your records and is not a medical diagnosis.',
        ];
    }

    public function wellness(array $request): array
    {
        $this->lastWellnessRequest = $request;

        if ($this->fail) {
            throw new RuntimeException('Sensitive fake provider failure');
        }

        return $this->wellnessResponse ?? [
            'summary' => 'Your daily records show steady wellness tracking.',
            'suggestion' => 'Complete one remaining wellness task at a comfortable pace.',
            'motivation' => 'Small consistent actions can support your daily routine.',
            'disclaimer' => 'This is general wellness information, not medical advice.',
        ];
    }
}
