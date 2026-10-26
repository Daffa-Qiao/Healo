<?php

namespace Tests\Feature;

use App\Contracts\HealthAIProviderInterface;
use App\Enums\SymptomUrgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeHealthAIProvider;
use Tests\TestCase;

class SymptomCheckerFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_run_ephemeral_or_saved_assessment_and_delete_it(): void
    {
        $this->app->instance(HealthAIProviderInterface::class, new FakeHealthAIProvider);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/symptom-checker', [
            'symptoms' => 'Sakit kepala ringan sejak pagi.',
        ])->assertOk()->assertSee('Symptoms require general monitoring.');
        $this->assertDatabaseCount('symptom_assessments', 0);

        $this->actingAs($user)->post('/symptom-checker', [
            'symptoms' => 'Sakit kepala ringan sejak pagi.', 'save' => '1',
        ])->assertRedirect();
        $assessment = $user->symptomAssessments()->firstOrFail();
        $this->assertSame(SymptomUrgency::Routine, $assessment->urgency);

        $this->actingAs($user)->get('/symptom-checker/'.$assessment->id)
            ->assertOk()->assertSee('hydration-related symptoms');
        $this->actingAs($user)->delete('/symptom-checker/'.$assessment->id)->assertRedirect('/symptom-checker/history');
        $this->assertDatabaseMissing('symptom_assessments', ['id' => $assessment->id]);
    }

    public function test_emergency_and_self_harm_red_flags_override_provider(): void
    {
        $this->app->instance(HealthAIProviderInterface::class, new FakeHealthAIProvider);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/symptom-checker', [
            'symptoms' => 'Saya tidak bisa bernapas dan mengalami nyeri dada hebat.', 'save' => '1',
        ])->assertRedirect();
        $physical = $user->symptomAssessments()->latest('id')->firstOrFail();
        $this->assertSame(SymptomUrgency::Emergency, $physical->urgency);
        $this->assertContains('severe_difficulty_breathing', $physical->red_flags);

        $this->actingAs($user)->post('/symptom-checker', [
            'symptoms' => 'Saya ingin menyakiti diri dan bunuh diri.', 'save' => '1',
        ])->assertRedirect();
        $selfHarm = $user->symptomAssessments()->latest('id')->firstOrFail();
        $this->assertContains('self_harm_emergency', $selfHarm->red_flags);
    }

    public function test_invalid_provider_response_uses_validated_safe_fallback(): void
    {
        $this->app->instance(HealthAIProviderInterface::class, new FakeHealthAIProvider(assessment: [
            'summary' => 'Bad schema', 'urgency' => 'certainly-fatal',
        ]));
        $user = User::factory()->create();

        $this->actingAs($user)->post('/symptom-checker', [
            'symptoms' => 'Keluhan biasa tetapi memburuk.', 'save' => '1',
        ])->assertRedirect();

        $assessment = $user->symptomAssessments()->firstOrFail();
        $this->assertSame('fallback', $assessment->provider);
        $this->assertSame(SymptomUrgency::Soon, $assessment->urgency);
    }

    public function test_assessment_is_saved_to_journal_only_after_explicit_confirmation(): void
    {
        $user = User::factory()->create();
        $assessment = $user->symptomAssessments()->create([
            'input' => 'Sakit kepala ringan.', 'summary' => 'Ringkasan aman.', 'urgency' => 'routine',
            'red_flags' => [], 'symptoms' => [['name' => 'headache', 'severity' => 'mild']],
            'possible_categories' => ['general concern'], 'recommendation' => 'Pantau gejala.',
            'confidence' => 'low', 'provider' => 'local_demo', 'model' => 'rules',
        ]);

        $this->actingAs($user)->post('/symptom-checker/'.$assessment->id.'/journal')->assertSessionHasErrors('confirm');
        $this->assertDatabaseCount('health_journals', 0);

        $this->actingAs($user)->post('/symptom-checker/'.$assessment->id.'/journal', ['confirm' => '1'])
            ->assertRedirect();
        $this->assertDatabaseCount('health_journals', 1);
        $this->assertNotNull($assessment->refresh()->saved_to_journal_at);
    }
}
