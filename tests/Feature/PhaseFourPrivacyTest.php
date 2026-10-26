<?php

namespace Tests\Feature;

use App\Contracts\HealthAIProviderInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fakes\FakeHealthAIProvider;
use Tests\TestCase;

class PhaseFourPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_conversation_and_assessment_fields_are_encrypted_at_rest(): void
    {
        $user = User::factory()->create();
        $conversation = $user->healthConversations()->create(['title' => 'Private symptom title']);
        $message = $conversation->messages()->create([
            'role' => 'user', 'message_type' => 'chat', 'content' => 'Private symptom message',
            'metadata' => ['private' => 'metadata'],
        ]);
        $assessment = $user->symptomAssessments()->create([
            'input' => 'Private assessment input', 'summary' => 'Private summary', 'urgency' => 'routine',
            'red_flags' => [], 'symptoms' => [['name' => 'private symptom', 'severity' => 'mild']],
            'possible_categories' => ['private category'], 'recommendation' => 'Private recommendation',
            'differential' => [['rank' => 1, 'condition' => 'Private differential', 'specialty' => 'Private specialty', 'probability' => 0.5]],
            'recommended_specialty' => 'Private specialty', 'referral_needed' => true,
            'confidence' => 'low', 'provider' => 'local_demo', 'model' => 'rules',
        ]);

        $this->assertNotSame('Private symptom title', DB::table('health_conversations')->where('id', $conversation->id)->value('title'));
        $this->assertNotSame('Private symptom message', DB::table('health_messages')->where('id', $message->id)->value('content'));
        $this->assertNotSame('Private assessment input', DB::table('symptom_assessments')->where('id', $assessment->id)->value('input'));
        $this->assertNotSame('Private specialty', DB::table('symptom_assessments')->where('id', $assessment->id)->value('recommended_specialty'));
        $this->assertStringNotContainsString('Private differential', DB::table('symptom_assessments')->where('id', $assessment->id)->value('differential'));
        $this->assertSame('Private symptom message', $message->fresh()->content);
    }

    public function test_audit_log_does_not_store_health_message_content(): void
    {
        $this->app->instance(HealthAIProviderInterface::class, new FakeHealthAIProvider);
        $user = User::factory()->create();
        $conversation = $user->healthConversations()->create(['title' => 'Private']);
        $sensitive = 'My uniquely sensitive symptom 8f021';

        $this->actingAs($user)->post('/health-assistant/conversations/'.$conversation->id.'/messages', [
            'message' => $sensitive,
        ])->assertRedirect();

        $serializedLogs = DB::table('audit_logs')->get()->toJson();
        $this->assertStringNotContainsString($sensitive, $serializedLogs);
        $this->assertNull(DB::table('audit_logs')->latest('id')->value('safe_metadata'));
    }
}
