<?php

namespace Tests\Feature;

use App\Models\HealthyMenu;
use App\Models\NutritionProfile;
use App\Models\User;
use App\Services\Nutrition\MealService;
use Database\Seeders\HealthyMenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NutritionDashboardAndMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_nutrition_dashboard_shows_real_daily_and_weekly_data(): void
    {
        $user = User::factory()->create();
        NutritionProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);
        $service = app(MealService::class);
        $service->createWithItems($user, ['meal_date' => today(), 'meal_type' => 'breakfast'], [
            ['food_name' => 'Sarapan', 'serving_size' => 1, 'serving_unit' => 'porsi', 'calories' => 450, 'source' => 'manual'],
        ]);
        $service->createWithItems($user, ['meal_date' => today()->subDay(), 'meal_type' => 'lunch'], [
            ['food_name' => 'Makan siang', 'serving_size' => 1, 'serving_unit' => 'porsi', 'calories' => 600, 'source' => 'manual'],
        ]);

        $this->actingAs($user)->get('/nutrition')
            ->assertOk()
            ->assertSee('450')
            ->assertSee('2,000')
            ->assertSee(__('nutrition.weekly_trend'));

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('450');
    }

    public function test_healthy_menu_can_be_searched_and_filtered(): void
    {
        $this->seed(HealthyMenuSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)->get('/healthy-menus?q=oat&meal_type=breakfast&diet_preference=vegetarian')
            ->assertOk()->assertSee('Oat Pisang')->assertDontSee('Ikan Panggang');

        $this->assertSame(6, HealthyMenu::count());
    }
}
