<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthInsightsRequest;
use App\Http\Resources\HealthInsightsResource;
use App\Services\Insights\HealthInsightsService;

class HealthInsightsController extends Controller
{
    public function index(HealthInsightsRequest $request, HealthInsightsService $service): HealthInsightsResource
    {
        $period = $request->period();

        return new HealthInsightsResource([
            'overview' => $service->overview($request->user(), $period),
            'personalized' => $service->personalized($request->user(), $period)->toArray(),
        ]);
    }

    public function trends(HealthInsightsRequest $request, HealthInsightsService $service): HealthInsightsResource
    {
        return new HealthInsightsResource($service->trends($request->user(), $request->period()));
    }

    public function weekly(HealthInsightsRequest $request, HealthInsightsService $service): HealthInsightsResource
    {
        return new HealthInsightsResource($service->weekly($request->user()));
    }
}
