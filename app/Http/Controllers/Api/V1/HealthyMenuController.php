<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DietPreference;
use App\Enums\MealType;
use App\Http\Controllers\Controller;
use App\Http\Resources\HealthyMenuResource;
use App\Services\Nutrition\HealthyMenuService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class HealthyMenuController extends Controller
{
    public function index(Request $request, HealthyMenuService $service): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'meal_type' => ['nullable', Rule::enum(MealType::class)],
            'diet_preference' => ['nullable', Rule::enum(DietPreference::class)],
            'min_calories' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'max_calories' => ['nullable', 'numeric', 'min:0', 'max:5000', 'gte:min_calories'],
        ]);

        return HealthyMenuResource::collection($service->search($filters));
    }
}
