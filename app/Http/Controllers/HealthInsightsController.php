<?php

namespace App\Http\Controllers;

use App\Http\Requests\HealthInsightsRequest;
use App\Services\Insights\HealthInsightsService;
use Illuminate\View\View;

class HealthInsightsController extends Controller
{
    public function __invoke(HealthInsightsRequest $request, HealthInsightsService $service): View
    {
        $period = $request->period();

        return view('health-insights.index', [
            'period' => $period,
            'overview' => $service->overview($request->user(), $period),
            'trends' => $service->trends($request->user(), $period),
            'weekly' => $service->weekly($request->user()),
            'personalized' => $service->personalized($request->user(), $period),
        ]);
    }
}
