<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MealItemRequest;
use App\Http\Resources\MealItemResource;
use App\Models\MealItem;
use App\Services\Audit\AuditLogService;
use App\Services\Nutrition\MealItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealItemController extends Controller
{
    public function store(
        MealItemRequest $request,
        int $meal,
        MealItemService $service,
        AuditLogService $audit,
    ): JsonResponse {
        $meal = $request->user()->meals()->findOrFail($meal);
        $this->authorize('update', $meal);
        $item = $service->create($meal, $request->validated());
        $audit->record($request->user(), 'meal_item.api_created', $item, request: $request);

        return (new MealItemResource($item))->response()->setStatusCode(201);
    }

    public function update(
        MealItemRequest $request,
        int $mealItem,
        MealItemService $service,
        AuditLogService $audit,
    ): MealItemResource {
        $item = $this->ownedItem($request, $mealItem);
        $this->authorize('update', $item);
        $item = $service->update($item, $request->validated());
        $audit->record($request->user(), 'meal_item.api_updated', $item, request: $request);

        return new MealItemResource($item);
    }

    public function destroy(
        Request $request,
        int $mealItem,
        MealItemService $service,
        AuditLogService $audit,
    ): JsonResponse {
        $item = $request->user()->mealItems()->findOrFail($mealItem);
        $this->authorize('delete', $item);
        $audit->record($request->user(), 'meal_item.api_deleted', $item, request: $request);
        $service->delete($item);

        return response()->json(status: 204);
    }

    private function ownedItem(MealItemRequest $request, int $id): MealItem
    {
        return $request->user()->mealItems()->findOrFail($id);
    }
}
