<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MealRequest;
use App\Http\Resources\MealResource;
use App\Models\Meal;
use App\Services\Audit\AuditLogService;
use App\Services\Nutrition\MealService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MealController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return MealResource::collection(
            $request->user()->meals()->with('items')->latest('meal_date')->paginate(20)
        );
    }

    public function store(MealRequest $request, MealService $service, AuditLogService $audit): JsonResponse
    {
        $meal = $service->create($request->user(), $request->validated());
        $audit->record($request->user(), 'meal.api_created', $meal, request: $request);

        return (new MealResource($meal->load('items')))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $meal): MealResource
    {
        $meal = $this->ownedMeal($request, $meal);
        $this->authorize('view', $meal);

        return new MealResource($meal->load('items'));
    }

    public function update(MealRequest $request, int $meal, MealService $service, AuditLogService $audit): MealResource
    {
        $meal = $this->ownedMeal($request, $meal);
        $this->authorize('update', $meal);
        $meal = $service->update($meal, $request->validated());
        $audit->record($request->user(), 'meal.api_updated', $meal, request: $request);

        return new MealResource($meal);
    }

    public function destroy(Request $request, int $meal, MealService $service, AuditLogService $audit): JsonResponse
    {
        $meal = $this->ownedMeal($request, $meal);
        $this->authorize('delete', $meal);
        $audit->record($request->user(), 'meal.api_deleted', $meal, request: $request);
        $service->delete($meal);

        return response()->json(status: 204);
    }

    private function ownedMeal(Request $request, int $id): Meal
    {
        return $request->user()->meals()->findOrFail($id);
    }
}
