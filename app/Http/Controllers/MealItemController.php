<?php

namespace App\Http\Controllers;

use App\Http\Requests\MealItemRequest;
use App\Models\MealItem;
use App\Services\Audit\AuditLogService;
use App\Services\Nutrition\MealItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MealItemController extends Controller
{
    public function store(
        MealItemRequest $request,
        int $meal,
        MealItemService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $meal = $request->user()->meals()->findOrFail($meal);
        $this->authorize('update', $meal);
        $item = $service->create($meal, $request->validated());
        $audit->record($request->user(), 'meal_item.created', $item, request: $request);

        return redirect()->route('meals.show', $meal)->with('status', 'meal-item-created');
    }

    public function update(
        MealItemRequest $request,
        int $mealItem,
        MealItemService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $item = $this->ownedItem($request, $mealItem);
        $this->authorize('update', $item);
        $item = $service->update($item, $request->validated());
        $audit->record($request->user(), 'meal_item.updated', $item, request: $request);

        return redirect()->route('meals.show', $item->meal_id)->with('status', 'meal-item-updated');
    }

    public function destroy(
        Request $request,
        int $mealItem,
        MealItemService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $item = $this->ownedItem($request, $mealItem);
        $this->authorize('delete', $item);
        $mealId = $item->meal_id;
        $audit->record($request->user(), 'meal_item.deleted', $item, request: $request);
        $service->delete($item);

        return redirect()->route('meals.show', $mealId)->with('status', 'meal-item-deleted');
    }

    private function ownedItem(Request $request, int $id): MealItem
    {
        return $request->user()->mealItems()->findOrFail($id);
    }
}
