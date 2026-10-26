<?php

namespace Tests\Feature;

use App\Models\NutritionProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MealFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_meals_items_totals_and_daily_summary(): void
    {
        $user = User::factory()->create();
        NutritionProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

        $this->actingAs($user)->post('/meals', [
            'meal_date' => today()->toDateString(),
            'meal_type' => 'breakfast',
            'notes' => '<b>Setelah olahraga</b>',
        ])->assertSessionHasNoErrors();
        $meal = $user->meals()->firstOrFail();
        $this->assertSame('Setelah olahraga', $meal->notes);

        $this->actingAs($user)->post('/meals/'.$meal->id.'/items', [
            'food_name' => 'Nasi putih', 'serving_size' => 150, 'serving_unit' => 'g',
            'calories' => 195, 'protein_g' => 4, 'carbohydrates_g' => 42, 'fat_g' => 0.5,
        ])->assertSessionHasNoErrors();
        $first = $meal->items()->firstOrFail();

        $this->actingAs($user)->post('/meals/'.$meal->id.'/items', [
            'food_name' => 'Ayam panggang', 'serving_size' => 100, 'serving_unit' => 'g',
            'calories' => 220, 'protein_g' => 28, 'carbohydrates_g' => 2, 'fat_g' => 11,
        ])->assertSessionHasNoErrors();

        $this->assertSame('415.00', $meal->refresh()->total_calories);
        $summary = $user->nutritionDailySummaries()->firstOrFail();
        $this->assertSame('415.00', $summary->calories_consumed);
        $this->assertSame('1585.00', $summary->calories_remaining);

        $this->actingAs($user)->put('/meal-items/'.$first->id, [
            'food_name' => 'Nasi putih', 'serving_size' => 100, 'serving_unit' => 'g',
            'calories' => 130,
        ])->assertSessionHasNoErrors();
        $this->assertSame('350.00', $meal->refresh()->total_calories);

        $rawNotes = DB::table('meals')->where('id', $meal->id)->value('notes');
        $this->assertStringNotContainsString('Setelah olahraga', $rawNotes);

        $this->actingAs($user)->delete('/meal-items/'.$first->id)->assertSessionHasNoErrors();
        $this->actingAs($user)->delete('/meals/'.$meal->id)->assertRedirect('/meals');
        $this->assertSoftDeleted('meals', ['id' => $meal->id]);
        $this->assertSame('0.00', $summary->refresh()->calories_consumed);
    }

    public function test_meal_item_validation_rejects_negative_and_absurd_values(): void
    {
        $user = User::factory()->create();
        $meal = $user->meals()->create(['meal_date' => today(), 'meal_type' => 'lunch']);

        $this->actingAs($user)->post('/meals/'.$meal->id.'/items', [
            'food_name' => '', 'serving_size' => 0, 'serving_unit' => '',
            'calories' => -1, 'protein_g' => -3,
        ])->assertSessionHasErrors(['food_name', 'serving_size', 'serving_unit', 'calories', 'protein_g']);
    }
}
