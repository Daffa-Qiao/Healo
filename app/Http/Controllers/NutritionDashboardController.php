<?php

namespace App\Http\Controllers;

use App\Enums\MealType;
use App\Services\Nutrition\HealthyMenuService;
use App\Services\Nutrition\NutritionSummaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NutritionDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        NutritionSummaryService $summaries,
        HealthyMenuService $menus,
    ): View {
        $user = $request->user();
        $today = $summaries->refresh($user, today());
        $weekly = $summaries->weekly($user);
        $todayMeals = $user->meals()->with('items')->whereDate('meal_date', today())->latest()->get();
        $byType = collect(MealType::cases())->mapWithKeys(fn (MealType $type): array => [
            $type->value => round((float) $todayMeals->where('meal_type', $type)->sum('total_calories'), 2),
        ]);

        return view('nutrition.index', [
            'profile' => $user->nutritionProfile,
            'today' => $today,
            'weekly' => $weekly,
            'weeklyStats' => $summaries->weeklyStats($weekly),
            'todayMeals' => $todayMeals,
            'byType' => $byType,
            'mealTypes' => MealType::cases(),
            'recommendations' => $menus->recommendations($user),
        ]);
    }
}
