<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NutritionDailySummaryResource;
use App\Services\Nutrition\NutritionSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NutritionSummaryController extends Controller
{
    public function today(Request $request, NutritionSummaryService $service): NutritionDailySummaryResource
    {
        $summary = $service->refresh($request->user(), today());
        $this->authorize('view', $summary);

        return new NutritionDailySummaryResource($summary);
    }

    public function weekly(Request $request, NutritionSummaryService $service): JsonResponse
    {
        $days = $service->weekly($request->user());

        return response()->json([
            'data' => $days->map(fn (array $day): array => [
                'date' => $day['date']->toDateString(),
                'daily_target' => $day['target'],
                'consumed' => $day['consumed'],
                'remaining' => $day['remaining'],
                'meals_logged' => $day['meals'],
            ]),
            'meta' => $service->weeklyStats($days),
        ]);
    }
}
