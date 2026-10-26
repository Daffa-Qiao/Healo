<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseTwoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_two_api_requires_authentication(): void
    {
        $this->getJson('/api/v1/health-profile')->assertUnauthorized();
        $this->getJson('/api/v1/bmi-records')->assertUnauthorized();
        $this->getJson('/api/v1/health-journals')->assertUnauthorized();
    }

    public function test_user_can_manage_profile_bmi_and_journals_via_api(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/health-profile', [
            'height_cm' => 170,
            'weight_kg' => 65,
            'gender' => 'prefer_not_to_say',
            'health_conditions' => ['Asma'],
            'allergies' => [],
            'consent' => true,
            'user_id' => User::factory()->create()->id,
        ])->assertOk()
            ->assertJsonPath('data.height_cm', 170)
            ->assertJsonPath('data.health_conditions.0', 'Asma');

        $this->assertSame($user->id, $user->healthProfile()->firstOrFail()->user_id);

        $bmiResponse = $this->postJson('/api/v1/bmi-records', [
            'height_cm' => 170,
            'weight_kg' => 65,
        ])->assertCreated()
            ->assertJsonPath('data.category', 'healthy');
        $bmiId = $bmiResponse->json('data.id');

        $this->getJson('/api/v1/bmi-records/'.$bmiId)
            ->assertOk()->assertJsonPath('data.id', $bmiId);

        $journalResponse = $this->postJson('/api/v1/health-journals', [
            'title' => 'API Journal',
            'journal_date' => now()->toDateString(),
            'mood' => 'good',
            'complaint' => 'Lelah ringan',
            'symptoms' => ['Lelah'],
            'notes' => 'Istirahat cukup',
            'weight_kg' => 65.5,
            'systolic' => 120,
            'diastolic' => 80,
            'sleep_hours' => 8,
            'water_liters' => 2,
            'exercise_minutes' => 30,
        ])->assertCreated()
            ->assertJsonPath('data.title', 'API Journal')
            ->assertJsonPath('data.metrics.weight_kg', 65.5)
            ->assertJsonPath('data.metrics.systolic', 120)
            ->assertJsonPath('data.metrics.diastolic', 80);
        $journalId = $journalResponse->json('data.id');

        $entryResponse = $this->postJson('/api/v1/health-journals/'.$journalId.'/entries', [
            'type' => 'note',
            'content' => 'Minum air',
        ])->assertCreated();
        $entryId = $entryResponse->json('data.id');

        $this->putJson('/api/v1/health-journal-entries/'.$entryId, [
            'type' => 'note',
            'content' => 'Minum air cukup',
        ])->assertOk()->assertJsonPath('data.content', 'Minum air cukup');

        $this->getJson('/api/v1/health-journals/'.$journalId)
            ->assertOk()->assertJsonPath('data.title', 'API Journal');
        $this->deleteJson('/api/v1/health-journal-entries/'.$entryId)->assertNoContent();
        $this->deleteJson('/api/v1/bmi-records/'.$bmiId)->assertNoContent();
        $this->deleteJson('/api/v1/health-journals/'.$journalId)->assertNoContent();
    }

    public function test_api_returns_validation_errors(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/bmi-records', ['height_cm' => 10])
            ->assertUnprocessable()->assertJsonValidationErrors(['height_cm', 'weight_kg']);
        $this->postJson('/api/v1/health-journals', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['title', 'journal_date']);
    }
}
