<?php

namespace Tests\Feature;

use App\Enums\FoodAnalysisStatus;
use App\Models\Meal;
use App\Models\NutritionProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseThreeAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_or_modify_another_users_nutrition_data_on_web(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $meal = Meal::factory()->for($owner)->create();
        $item = $meal->items()->create([
            'food_name' => 'Private food', 'serving_size' => 1, 'serving_unit' => 'porsi', 'calories' => 100,
        ]);
        $analysis = $owner->foodAnalyses()->create([
            'status' => FoodAnalysisStatus::Review,
            'result' => ['foods' => []],
            'confidence' => 0.5,
        ]);

        $this->actingAs($intruder)->get('/meals/'.$meal->id)->assertNotFound();
        $this->actingAs($intruder)->put('/meals/'.$meal->id, $this->mealPayload())->assertNotFound();
        $this->actingAs($intruder)->delete('/meals/'.$meal->id)->assertNotFound();
        $this->actingAs($intruder)->put('/meal-items/'.$item->id, $this->itemPayload())->assertNotFound();
        $this->actingAs($intruder)->get('/food-analysis/'.$analysis->id)->assertNotFound();
        $this->actingAs($intruder)->get('/food-analysis/'.$analysis->id.'/image')->assertNotFound();
    }

    public function test_api_owner_scoping_returns_not_found_for_foreign_resources(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $meal = Meal::factory()->for($owner)->create();
        $item = $meal->items()->create([
            'food_name' => 'Private food', 'serving_size' => 1, 'serving_unit' => 'porsi', 'calories' => 100,
        ]);
        $analysis = $owner->foodAnalyses()->create([
            'status' => FoodAnalysisStatus::Review, 'result' => ['foods' => []], 'confidence' => 0.5,
        ]);
        Sanctum::actingAs($intruder);

        $this->getJson('/api/v1/meals/'.$meal->id)->assertNotFound();
        $this->putJson('/api/v1/meals/'.$meal->id, $this->mealPayload())->assertNotFound();
        $this->deleteJson('/api/v1/meals/'.$meal->id)->assertNotFound();
        $this->putJson('/api/v1/meal-items/'.$item->id, $this->itemPayload())->assertNotFound();
        $this->getJson('/api/v1/nutrition/food-analysis/'.$analysis->id)->assertNotFound();
    }

    public function test_admin_has_no_private_nutrition_bypass(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $profile = NutritionProfile::factory()->for($owner)->create();
        $meal = Meal::factory()->for($owner)->create();
        $item = $meal->items()->create([
            'food_name' => 'Private food', 'serving_size' => 1, 'serving_unit' => 'porsi', 'calories' => 100,
        ]);
        $summary = $owner->nutritionDailySummaries()->create([
            'summary_date' => today(), 'calorie_target' => 2000, 'calories_consumed' => 100,
            'calories_remaining' => 1900, 'meals_logged' => 1,
        ]);
        $analysis = $owner->foodAnalyses()->create([
            'status' => FoodAnalysisStatus::Review, 'result' => ['foods' => []], 'confidence' => 0.5,
        ]);
        $this->actingAs($admin);

        $this->assertFalse(Gate::allows('view', $profile));
        $this->assertFalse(Gate::allows('view', $meal));
        $this->assertFalse(Gate::allows('view', $item));
        $this->assertFalse(Gate::allows('view', $summary));
        $this->assertFalse(Gate::allows('view', $analysis));

        $this->actingAs(User::factory()->create());
        $this->assertFalse(Gate::allows('view', $profile));
    }

    private function mealPayload(): array
    {
        return ['meal_date' => today()->toDateString(), 'meal_type' => 'lunch', 'notes' => 'Unauthorized'];
    }

    private function itemPayload(): array
    {
        return ['food_name' => 'Unauthorized', 'serving_size' => 1, 'serving_unit' => 'porsi', 'calories' => 100];
    }
}
