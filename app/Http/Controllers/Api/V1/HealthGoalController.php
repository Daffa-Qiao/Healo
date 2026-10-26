<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHealthGoalRequest;
use App\Http\Requests\UpdateHealthGoalRequest;
use App\Http\Resources\HealthGoalResource;
use App\Models\HealthGoal;
use App\Services\Audit\AuditLogService;
use App\Services\Insights\HealthGoalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HealthGoalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $goals = $request->user()->healthGoals()->latest()->paginate(20);
        $goals->getCollection()->each(fn (HealthGoal $goal) => $goal->setRelation('user', $request->user()));

        return HealthGoalResource::collection($goals);
    }

    public function store(StoreHealthGoalRequest $request, HealthGoalService $service, AuditLogService $audit): JsonResponse
    {
        $goal = $service->create($request->user(), $request->validated());
        $goal->setRelation('user', $request->user());
        $audit->record($request->user(), 'health_goal.api_created', $goal, request: $request);

        return (new HealthGoalResource($goal))->response()->setStatusCode(201);
    }

    public function show(Request $request, HealthGoal $goal): HealthGoalResource
    {
        $this->authorize('view', $goal);
        $goal->setRelation('user', $request->user());

        return new HealthGoalResource($goal);
    }

    public function update(UpdateHealthGoalRequest $request, HealthGoal $goal, HealthGoalService $service, AuditLogService $audit): HealthGoalResource
    {
        $goal = $service->update($goal, $request->validated());
        $goal->setRelation('user', $request->user());
        $audit->record($request->user(), 'health_goal.api_updated', $goal, request: $request);

        return new HealthGoalResource($goal);
    }

    public function destroy(Request $request, HealthGoal $goal, HealthGoalService $service, AuditLogService $audit): JsonResponse
    {
        $this->authorize('delete', $goal);
        $audit->record($request->user(), 'health_goal.api_deleted', $goal, request: $request);
        $service->delete($goal);

        return response()->json(status: 204);
    }
}
