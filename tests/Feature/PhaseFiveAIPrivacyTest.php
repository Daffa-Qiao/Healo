<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PhaseFiveAIPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_key_and_identity_are_not_exposed_to_frontend_or_groq_context(): void
    {
        config()->set('health_ai.provider', 'groq');
        config()->set('services.groq.api_key', 'groq-ultra-secret-key');
        config()->set('services.groq.model', 'openai/gpt-oss-20b');
        Http::fake(['api.groq.com/*' => Http::response([
            'choices' => [['message' => ['content' => json_encode([
                'summary' => 'Safe aggregate summary.', 'insights' => [], 'recommendations' => [],
                'disclaimer' => 'Not a diagnosis.',
            ])]]],
        ])]);
        $user = User::factory()->create(['email' => 'private-identity@example.test']);

        $response = $this->actingAs($user)->get('/health-insights');

        $response->assertOk()
            ->assertDontSee('groq-ultra-secret-key')
            ->assertDontSee('private-identity@example.test');
        Http::assertSent(function (Request $request): bool {
            $serialized = json_encode($request->data());

            return ! str_contains($serialized, 'private-identity@example.test')
                && ! str_contains($serialized, 'groq-ultra-secret-key');
        });
    }
}
