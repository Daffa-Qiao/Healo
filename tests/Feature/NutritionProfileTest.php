<?php

namespace Tests\Feature;

use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NutritionProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_calculate_and_update_a_nutrition_profile_from_health_data(): void
    {
        $user = User::factory()->create();
        HealthProfile::factory()->for($user)->create([
            'height_cm' => 165,
            'weight_kg' => 60,
            'birth_date' => now()->subYears(30)->toDateString(),
            'gender' => 'female',
        ]);

        $this->actingAs($user)->put('/nutrition/profile', [
            'activity_level' => 'moderate',
            'goal' => 'maintain',
            'diet_preference' => 'vegetarian',
            'food_preferences' => "Tempe\nSayuran",
            'recalculate' => true,
        ])->assertSessionHasNoErrors()->assertRedirect('/nutrition/profile');

        $profile = $user->nutritionProfile()->firstOrFail();
        $this->assertSame(2046, $profile->daily_calorie_target);
        $this->assertSame(['Tempe', 'Sayuran'], $profile->food_preferences);
        $this->assertSame('calculated', $profile->target_source);

        $raw = DB::table('nutrition_profiles')->where('id', $profile->id)->value('food_preferences');
        $this->assertStringNotContainsString('Tempe', $raw);
    }

    public function test_user_can_set_a_safe_manual_target_without_reentering_health_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/nutrition/profile', [
            'activity_level' => 'light',
            'goal' => 'maintain',
            'diet_preference' => 'balanced',
            'daily_calorie_target' => 1900,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1900, $user->nutritionProfile()->firstOrFail()->daily_calorie_target);
    }

    public function test_nutrition_profile_validation_rejects_unsafe_values(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/nutrition/profile', [
            'activity_level' => 'impossible',
            'goal' => 'starvation',
            'diet_preference' => 'unknown',
            'daily_calorie_target' => 300,
        ])->assertSessionHasErrors(['activity_level', 'goal', 'diet_preference', 'daily_calorie_target']);

        $this->assertDatabaseCount('nutrition_profiles', 0);
    }
}
