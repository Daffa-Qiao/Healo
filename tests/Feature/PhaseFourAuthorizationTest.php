<?php

namespace Tests\Feature;

use App\Models\HealthConversation;
use App\Models\SymptomAssessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseFourAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_message_into_or_delete_another_users_conversation(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $conversation = HealthConversation::factory()->for($owner)->create();

        $this->actingAs($intruder)->get('/health-assistant/conversations/'.$conversation->id)->assertForbidden();
        $this->actingAs($intruder)->post('/health-assistant/conversations/'.$conversation->id.'/messages', ['message' => 'Intrusion'])->assertForbidden();
        $this->actingAs($intruder)->delete('/health-assistant/conversations/'.$conversation->id)->assertForbidden();
        $this->assertDatabaseHas('health_conversations', ['id' => $conversation->id]);
    }

    public function test_user_cannot_access_or_delete_another_users_assessment_via_web_or_api(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $assessment = SymptomAssessment::factory()->for($owner)->create();

        $this->actingAs($intruder)->get('/symptom-checker/'.$assessment->id)->assertForbidden();
        $this->actingAs($intruder)->delete('/symptom-checker/'.$assessment->id)->assertForbidden();

        Sanctum::actingAs($intruder);
        $this->getJson('/api/v1/symptom-checker/'.$assessment->id)->assertForbidden();
        $this->deleteJson('/api/v1/symptom-checker/'.$assessment->id)->assertForbidden();
    }

    public function test_admin_has_no_private_ai_data_bypass(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $conversation = HealthConversation::factory()->for($owner)->create();
        $message = $conversation->messages()->create([
            'role' => 'user', 'message_type' => 'chat', 'content' => 'Private content',
        ]);
        $assessment = SymptomAssessment::factory()->for($owner)->create();
        $this->actingAs($admin);

        $this->assertFalse(Gate::allows('view', $conversation));
        $this->assertFalse(Gate::allows('view', $message));
        $this->assertFalse(Gate::allows('view', $assessment));
    }
}
