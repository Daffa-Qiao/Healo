<?php

namespace App\Http\Controllers;

use App\Enums\DietPreference;
use App\Enums\MealType;
use App\Services\Nutrition\HealthyMenuService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HealthyMenuController extends Controller
{
    public function index(Request $request, HealthyMenuService $service): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'meal_type' => ['nullable', Rule::enum(MealType::class)],
            'diet_preference' => ['nullable', Rule::enum(DietPreference::class)],
            'min_calories' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'max_calories' => ['nullable', 'numeric', 'min:0', 'max:5000', 'gte:min_calories'],
        ]);

        return view('healthy-menus.index', [
            'menus' => $service->search($filters),
            'recommendations' => $service->recommendations($request->user(), $filters['meal_type'] ?? null),
            'mealTypes' => MealType::cases(),
            'dietPreferences' => DietPreference::cases(),
        ]);
    }
}
