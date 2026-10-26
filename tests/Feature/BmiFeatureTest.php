<?php

namespace Tests\Feature;

use App\Enums\BmiCategory;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BmiFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_calculate_view_and_delete_bmi_history(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/bmi', [
            'height_cm' => 175,
            'weight_kg' => 70,
            'measured_at' => now()->subHour()->toDateTimeString(),
        ]);

        $record = $user->bmiRecords()->firstOrFail();
        $response->assertSessionHasNoErrors()->assertRedirect(route('bmi.index', ['result' => $record->id]));
        $this->assertSame('22.86', $record->bmi);
        $this->assertSame(BmiCategory::Healthy, $record->category);
        $this->actingAs($user)->get('/bmi')->assertOk()->assertSee('22.9');
        $this->actingAs($user)->delete('/bmi/'.$record->id)->assertRedirect('/bmi');
        $this->assertDatabaseMissing('bmi_records', ['id' => $record->id]);
    }

    public function test_bmi_can_update_an_existing_profile_but_does_not_create_one_without_consent(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/bmi', [
            'height_cm' => 180,
            'weight_kg' => 75,
            'update_health_profile' => true,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('health_profiles', 0);

        $profile = HealthProfile::factory()->for($user)->create();
        $this->actingAs($user)->post('/bmi', [
            'height_cm' => 181,
            'weight_kg' => 76,
            'update_health_profile' => true,
        ])->assertSessionHasNoErrors();

        $this->assertSame('181.00', $profile->refresh()->height_cm);
    }

    public function test_bmi_validation_rejects_impossible_or_future_measurements(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/bmi', [
            'height_cm' => 20,
            'weight_kg' => 500,
            'measured_at' => now()->addDay()->toDateTimeString(),
        ])->assertSessionHasErrors(['height_cm', 'weight_kg', 'measured_at']);

        $this->assertDatabaseCount('bmi_records', 0);
    }
}
