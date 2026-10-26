<?php

namespace Tests\Feature;

use App\Models\HealthProfile;
use App\Models\User;
use App\Services\HealthAI\HealthAIContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthAIContextServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_context_is_minimized_by_topic_and_belongs_only_to_the_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        HealthProfile::factory()->for($user)->create(['weight_kg' => 61, 'health_conditions' => ['User condition']]);
        HealthProfile::factory()->for($other)->create(['weight_kg' => 99, 'health_conditions' => ['Other private condition']]);
        $user->nutritionDailySummaries()->create([
            'summary_date' => today(), 'calorie_target' => 1800, 'calories_consumed' => 900,
            'calories_remaining' => 900, 'meals_logged' => 2,
        ]);

        $service = app(HealthAIContextService::class);
        $general = $service->forAssistant($user, 'Halo');
        $nutrition = $service->forAssistant($user, 'Berapa kalori yang saya makan hari ini?');
        $assessment = $service->forAssessment($user);

        $this->assertSame([], $general);
        $this->assertSame(900.0, $nutrition['nutrition_today']['calories_consumed']);
        $this->assertArrayHasKey('age_range', $assessment['profile']);
        $this->assertArrayNotHasKey('weight_kg', $assessment['profile']);
        $this->assertNotContains('Other private condition', $assessment['known_conditions']);
    }
}
