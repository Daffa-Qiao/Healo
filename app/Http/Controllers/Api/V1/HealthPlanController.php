<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateHealthPlanRequest;
use App\Http\Resources\HealthPlanResource;
use App\Models\HealthPlan;
use App\Services\HealthPlan\HealthPlanPersonalizationService;
use App\Services\HealthPlan\HealthPlanService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthPlanController extends Controller
{
    public function index(Request $request, HealthPlanService $healthPlans): AnonymousResourceCollection
    {
        $this->authorize('viewAny', HealthPlan::class);
        $healthPlans->today($request->user(), false);
        $plans = $request->user()->healthPlans()->with('tasks')->latest('plan_date')->paginate(30);

        return HealthPlanResource::collection($plans);
    }

    public function today(Request $request, HealthPlanService $plans, HealthPlanPersonalizationService $personalization): JsonResource
    {
        $plan = $plans->today($request->user(), false);
        abort_unless($plan, 404, 'No health plan exists for today.');
        $this->authorize('view', $plan);

        return (new HealthPlanResource($plan))->additional([
            'personalization' => $personalization->forPlan($plan)->toArray(),
        ]);
    }

    public function generate(GenerateHealthPlanRequest $request, HealthPlanService $plans): HealthPlanResource
    {
        $plan = $plans->generate($request->user(), $request->validated('plan_date'));

        return new HealthPlanResource($plan);
    }
}
