<?php

namespace App\Http\Controllers;

use App\Enums\MealType;
use App\Http\Requests\MealRequest;
use App\Models\Meal;
use App\Services\Audit\AuditLogService;
use App\Services\Nutrition\MealService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MealController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'meal_type' => ['nullable', Rule::enum(MealType::class)],
        ]);
        $meals = $request->user()->meals()->with('items')
            ->when($validated['date'] ?? null, fn ($query, $date) => $query->whereDate('meal_date', $date))
            ->when($validated['meal_type'] ?? null, fn ($query, $type) => $query->where('meal_type', $type))
            ->latest('meal_date')->latest('id')->paginate(12)->withQueryString();

        return view('meals.index', ['meals' => $meals, 'mealTypes' => MealType::cases()]);
    }

    public function create(): View
    {
        return view('meals.create', ['mealTypes' => MealType::cases()]);
    }

    public function store(MealRequest $request, MealService $service, AuditLogService $audit): RedirectResponse
    {
        $meal = $service->create($request->user(), $request->validated());
        $audit->record($request->user(), 'meal.created', $meal, request: $request);

        return redirect()->route('meals.show', $meal)->with('status', 'meal-created');
    }

    public function show(Request $request, int $meal): View
    {
        $meal = $this->ownedMeal($request, $meal);
        $this->authorize('view', $meal);

        return view('meals.show', ['meal' => $meal->load('items')]);
    }

    public function edit(Request $request, int $meal): View
    {
        $meal = $this->ownedMeal($request, $meal);
        $this->authorize('update', $meal);

        return view('meals.edit', ['meal' => $meal, 'mealTypes' => MealType::cases()]);
    }

    public function update(MealRequest $request, int $meal, MealService $service, AuditLogService $audit): RedirectResponse
    {
        $meal = $this->ownedMeal($request, $meal);
        $this->authorize('update', $meal);
        $meal = $service->update($meal, $request->validated());
        $audit->record($request->user(), 'meal.updated', $meal, request: $request);

        return redirect()->route('meals.show', $meal)->with('status', 'meal-updated');
    }

    public function destroy(Request $request, int $meal, MealService $service, AuditLogService $audit): RedirectResponse
    {
        $meal = $this->ownedMeal($request, $meal);
        $this->authorize('delete', $meal);
        $audit->record($request->user(), 'meal.deleted', $meal, request: $request);
        $service->delete($meal);

        return redirect()->route('meals.index')->with('status', 'meal-deleted');
    }

    private function ownedMeal(Request $request, int $id): Meal
    {
        return $request->user()->meals()->findOrFail($id);
    }
}
