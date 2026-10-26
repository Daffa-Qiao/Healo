<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_update_their_health_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/health-profile', [
            'height_cm' => 165,
            'weight_kg' => 60,
            'birth_date' => '1995-05-14',
            'gender' => 'female',
            'activity_level' => 'moderate',
            'health_goal' => 'general_wellness',
            'health_conditions' => "Asma\nMigrain",
            'allergies' => 'Kacang tanah',
            'consent' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect('/health-profile');

        $profile = $user->healthProfile()->firstOrFail();
        $this->assertSame(['Asma', 'Migrain'], $profile->health_conditions);
        $this->assertSame(['Kacang tanah'], $profile->allergies);
        $this->assertNotNull($profile->consented_at);

        $this->actingAs($user)->put('/health-profile', [
            'height_cm' => 166,
            'weight_kg' => 59,
            'health_conditions' => [],
            'allergies' => [],
        ])->assertSessionHasNoErrors();

        $this->assertSame('166.00', $profile->refresh()->height_cm);
    }

    public function test_sensitive_profile_fields_are_encrypted_at_rest(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/health-profile', [
            'health_conditions' => ['Diabetes'],
            'allergies' => ['Penisilin'],
            'consent' => true,
        ])->assertSessionHasNoErrors();

        $raw = DB::table('health_profiles')->where('user_id', $user->id)->first();

        $this->assertStringNotContainsString('Diabetes', $raw->health_conditions);
        $this->assertStringNotContainsString('Penisilin', $raw->allergies);
        $this->assertSame(['Diabetes'], $user->healthProfile()->firstOrFail()->health_conditions);
    }

    public function test_consent_and_measurement_validation_are_enforced(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/health-profile', [
            'height_cm' => 20,
            'weight_kg' => 500,
            'birth_date' => now()->addDay()->toDateString(),
        ])->assertSessionHasErrors(['height_cm', 'weight_kg', 'birth_date', 'consent']);

        $this->assertDatabaseCount('health_profiles', 0);
    }
}
