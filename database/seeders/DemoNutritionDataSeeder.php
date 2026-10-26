<?php

namespace Database\Seeders;

use App\Enums\ActivityLevel;
use App\Enums\DietPreference;
use App\Enums\NutritionGoal;
use App\Enums\NutritionSource;
use App\Models\User;
use App\Services\Nutrition\MealService;
use Illuminate\Database\Seeder;

class DemoNutritionDataSeeder extends Seeder
{
    public function run(MealService $meals): void
    {
        $user = User::query()->where('email', 'demo@healo.test')->firstOrFail();
        $user->nutritionProfile()->updateOrCreate([], [
            'daily_calorie_target' => 1850,
            'activity_level' => ActivityLevel::Moderate,
            'goal' => NutritionGoal::Maintain,
            'diet_preference' => DietPreference::Balanced,
            'food_preferences' => ['Makanan rumahan', 'Sayuran'],
            'target_source' => 'calculated',
            'calculated_at' => now(),
        ]);

        $user->nutritionDailySummaries()->delete();
        $user->meals()->withTrashed()->forceDelete();

        $templates = [
            'breakfast' => [['food_name' => 'Oat dan pisang (data demo)', 'serving_size' => 1, 'serving_unit' => 'mangkuk', 'calories' => 360, 'protein_g' => 12, 'carbohydrates_g' => 62, 'fat_g' => 8]],
            'lunch' => [['food_name' => 'Nasi, ayam, dan sayur (data demo)', 'serving_size' => 1, 'serving_unit' => 'porsi', 'calories' => 560, 'protein_g' => 35, 'carbohydrates_g' => 68, 'fat_g' => 16]],
            'dinner' => [['food_name' => 'Ikan dan kentang (data demo)', 'serving_size' => 1, 'serving_unit' => 'porsi', 'calories' => 470, 'protein_g' => 32, 'carbohydrates_g' => 46, 'fat_g' => 15]],
            'snack' => [['food_name' => 'Buah dan yogurt (data demo)', 'serving_size' => 1, 'serving_unit' => 'porsi', 'calories' => 190, 'protein_g' => 8, 'carbohydrates_g' => 31, 'fat_g' => 4]],
        ];

        foreach (range(6, 0) as $daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            foreach ($templates as $type => $items) {
                if ($daysAgo > 0 && $type === 'snack' && $daysAgo % 2 === 0) {
                    continue;
                }
                $items = array_map(fn (array $item): array => [...$item, 'source' => NutritionSource::System], $items);
                $meals->createWithItems($user, [
                    'meal_date' => $date,
                    'meal_type' => $type,
                    'notes' => 'Data contoh untuk demonstrasi HEALO.',
                ], $items);
            }
        }
    }
}
