<?php

namespace Tests\Unit;

use App\Enums\SymptomUrgency;
use App\Exceptions\HealthAIException;
use App\Services\HealthAI\HealthAIResponseParser;
use Tests\TestCase;

class HealthAIResponseParserTest extends TestCase
{
    public function test_it_parses_a_valid_structured_assessment(): void
    {
        $result = app(HealthAIResponseParser::class)->assessment([
            'summary' => 'A safe summary.',
            'symptoms' => [['name' => 'fever', 'severity' => 'moderate']],
            'possible_categories' => ['infection-related symptoms'],
            'differential' => [[
                'rank' => 1,
                'condition' => 'infection-related symptoms',
                'specialty' => 'Internal Medicine',
                'probability' => 0.65,
            ]],
            'recommended_specialty' => 'Internal Medicine',
            'referral_needed' => true,
            'red_flags' => [],
            'urgency' => 'soon',
            'recommendation' => 'Consider medical advice.',
            'confidence' => 'low',
            'provider' => 'fake',
            'model' => 'fake-v1',
        ]);

        $this->assertSame(SymptomUrgency::Soon, $result->urgency);
        $this->assertSame('fever', $result->symptoms[0]['name']);
        $this->assertSame('Internal Medicine', $result->recommendedSpecialty);
        $this->assertTrue($result->referralNeeded);
    }

    public function test_it_rejects_invalid_provider_schema_and_urgency(): void
    {
        $this->expectException(HealthAIException::class);

        app(HealthAIResponseParser::class)->assessment([
            'summary' => 'Invalid',
            'symptoms' => 'not-an-array',
            'possible_categories' => [],
            'differential' => [],
            'recommended_specialty' => 'General Practitioner',
            'referral_needed' => false,
            'red_flags' => [],
            'urgency' => 'critical',
            'recommendation' => 'Invalid',
            'confidence' => 'certain',
            'provider' => 'fake',
            'model' => 'fake-v1',
        ]);
    }
}
