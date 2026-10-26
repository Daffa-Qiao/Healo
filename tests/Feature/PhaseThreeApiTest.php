<?php

namespace Tests\Feature;

use App\Contracts\FoodAnalysisProviderInterface;
use App\Models\HealthProfile;
use App\Models\User;
use Database\Seeders\HealthyMenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Fakes\FakeFoodAnalysisProvider;
use Tests\TestCase;

class PhaseThreeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_nutrition_api_requires_authentication(): void
    {
        $this->getJson('/api/v1/nutrition/profile')->assertUnauthorized();
        $this->getJson('/api/v1/nutrition/today')->assertUnauthorized();
        $this->getJson('/api/v1/meals')->assertUnauthorized();
    }

    public function test_user_can_manage_nutrition_profile_meals_items_and_summaries(): void
    {
        $user = User::factory()->create();
        HealthProfile::factory()->for($user)->create([
            'birth_date' => now()->subYears(30), 'gender' => 'female', 'height_cm' => 165, 'weight_kg' => 60,
        ]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/nutrition/profile', [
            'activity_level' => 'moderate', 'goal' => 'maintain', 'diet_preference' => 'balanced',
            'recalculate' => true, 'user_id' => User::factory()->create()->id,
        ])->assertOk()->assertJsonPath('data.daily_calorie_target', 2046);
        $this->assertSame($user->id, $user->nutritionProfile()->firstOrFail()->user_id);

        $mealResponse = $this->postJson('/api/v1/meals', [
            'meal_date' => today()->toDateString(), 'meal_type' => 'lunch', 'notes' => 'API meal',
        ])->assertCreated()->assertJsonPath('data.meal_type', 'lunch');
        $mealId = $mealResponse->json('data.id');

        $itemResponse = $this->postJson('/api/v1/meals/'.$mealId.'/items', [
            'food_name' => 'Tempe', 'serving_size' => 100, 'serving_unit' => 'g', 'calories' => 190,
            'protein_g' => 19, 'carbohydrates_g' => 8, 'fat_g' => 11,
        ])->assertCreated()->assertJsonPath('data.source', 'manual');
        $itemId = $itemResponse->json('data.id');

        $this->getJson('/api/v1/nutrition/today')
            ->assertOk()->assertJsonPath('data.consumed', 190)->assertJsonPath('data.meals_logged', 1);
        $this->getJson('/api/v1/nutrition/summary')->assertOk()->assertJsonCount(7, 'data');
        $this->getJson('/api/v1/meals/'.$mealId)->assertOk()->assertJsonCount(1, 'data.items');
        $this->putJson('/api/v1/meal-items/'.$itemId, [
            'food_name' => 'Tempe', 'serving_size' => 120, 'serving_unit' => 'g', 'calories' => 220,
        ])->assertOk()->assertJsonPath('data.calories', 220);
        $this->deleteJson('/api/v1/meal-items/'.$itemId)->assertNoContent();
        $this->deleteJson('/api/v1/meals/'.$mealId)->assertNoContent();
    }

    public function test_food_analysis_api_returns_structured_draft_and_requires_confirmation(): void
    {
        Storage::fake('local');
        $this->app->instance(FoodAnalysisProviderInterface::class, new FakeFoodAnalysisProvider);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/nutrition/food-analysis', [
            'image' => UploadedFile::fake()->image('meal.jpg'),
        ])->assertCreated()
            ->assertJsonPath('data.status', 'review')
            ->assertJsonPath('data.result.foods.0.name', 'Nasi putih')
            ->assertJsonPath('data.low_confidence', false);
        $analysisId = $response->json('data.id');
        $this->assertDatabaseCount('meals', 0);

        $this->postJson('/api/v1/nutrition/food-analysis/'.$analysisId.'/confirm', [
            'meal_date' => today()->toDateString(), 'meal_type' => 'dinner',
            'foods' => [['food_name' => 'Nasi putih', 'serving_size' => 150, 'serving_unit' => 'g', 'calories' => 195]],
        ])->assertCreated()->assertJsonPath('data.total_calories', 195);
    }

    public function test_menu_api_is_paginated_and_filterable(): void
    {
        $this->seed(HealthyMenuSeeder::class);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/nutrition/menus?meal_type=snack')
            ->assertOk()->assertJsonPath('data.0.meal_type', 'snack');
    }

    public function test_ai_analysis_endpoint_is_strictly_rate_limited(): void
    {
        Storage::fake('local');
        $this->app->instance(FoodAnalysisProviderInterface::class, new FakeFoodAnalysisProvider);
        Sanctum::actingAs(User::factory()->create());

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/v1/nutrition/food-analysis', [
                'image' => UploadedFile::fake()->image("meal-{$attempt}.jpg"),
            ])->assertCreated();
        }

        $this->postJson('/api/v1/nutrition/food-analysis', [
            'image' => UploadedFile::fake()->image('meal-6.jpg'),
        ])->assertTooManyRequests();
    }
}
