<?php

namespace Tests\Feature;

use App\Contracts\HealthAIProviderInterface;
use App\Enums\HealthMessageRole;
use App\Enums\HealthMessageType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Fakes\FakeHealthAIProvider;
use Tests\TestCase;

class PhaseFourApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_api_requires_sanctum_authentication(): void
    {
        $this->getJson('/api/v1/health-assistant/conversations')->assertUnauthorized();
        $this->postJson('/api/v1/symptom-checker/assess', ['symptoms' => 'Headache'])->assertUnauthorized();
    }

    public function test_conversation_and_message_api_returns_safe_structured_data_without_system_messages(): void
    {
        $this->app->instance(HealthAIProviderInterface::class, new FakeHealthAIProvider);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $created = $this->postJson('/api/v1/health-assistant/conversations', ['title' => 'API chat'])
            ->assertCreated()->assertJsonPath('data.title', 'API chat');
        $id = $created->json('data.id');
        $conversation = $user->healthConversations()->findOrFail($id);
        $conversation->messages()->create([
            'role' => HealthMessageRole::System, 'message_type' => HealthMessageType::Safety,
            'content' => 'Secret system instruction',
        ]);

        $this->postJson('/api/v1/health-assistant/conversations/'.$id.'/messages', [
            'message' => 'Explain a mild headache.',
        ])->assertCreated()
            ->assertJsonPath('data.role', 'assistant')
            ->assertJsonPath('data.provider', 'fake')
            ->assertJsonMissing(['content' => 'Secret system instruction']);

        $this->getJson('/api/v1/health-assistant/conversations/'.$id)
            ->assertOk()->assertJsonMissing(['content' => 'Secret system instruction']);
        $this->getJson('/api/v1/health-assistant/conversations')->assertOk();
        $this->deleteJson('/api/v1/health-assistant/conversations/'.$id)->assertNoContent();
    }

    public function test_symptom_api_validates_input_returns_structure_and_manages_history(): void
    {
        $provider = new FakeHealthAIProvider;
        $this->app->instance(HealthAIProviderInterface::class, $provider);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/symptom-checker/assess', ['symptoms' => 'x'])
            ->assertUnprocessable()->assertJsonValidationErrors('symptoms');

        $response = $this->postJson('/api/v1/symptom-checker/assess', [
            'symptoms' => 'Mild headache since morning.', 'save' => true,
        ])->assertCreated()
            ->assertJsonPath('data.urgency', 'routine')
            ->assertJsonPath('data.confidence', 'low')
            ->assertJsonPath('data.recommended_specialty', 'General Practitioner')
            ->assertJsonPath('data.referral_needed', true)
            ->assertJsonPath('data.differential.0.condition', 'hydration-related symptoms');
        $id = $response->json('data.id');

        $this->assertStringContainsString('HEALO Symptom AI', $provider->lastAssessmentRequest['system_instructions']);
        $this->assertStringContainsString('Do not reveal hidden reasoning', $provider->lastAssessmentRequest['system_instructions']);

        $this->getJson('/api/v1/symptom-checker/history')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/symptom-checker/'.$id)->assertOk()->assertJsonPath('data.id', $id);
        $this->deleteJson('/api/v1/symptom-checker/'.$id)->assertNoContent();
    }

    public function test_ai_message_endpoint_is_rate_limited_to_ten_requests_per_minute(): void
    {
        $this->app->instance(HealthAIProviderInterface::class, new FakeHealthAIProvider);
        $user = User::factory()->create();
        $conversation = $user->healthConversations()->create(['title' => 'Rate limit']);
        Sanctum::actingAs($user);

        foreach (range(1, 10) as $attempt) {
            $this->postJson('/api/v1/health-assistant/conversations/'.$conversation->id.'/messages', [
                'message' => 'Health question '.$attempt,
            ])->assertCreated();
        }

        $this->postJson('/api/v1/health-assistant/conversations/'.$conversation->id.'/messages', [
            'message' => 'One request too many',
        ])->assertTooManyRequests();
    }
}
