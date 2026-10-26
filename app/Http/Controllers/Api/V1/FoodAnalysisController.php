<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\FoodAnalysisException;
use App\Http\Controllers\Controller;
use App\Http\Requests\FoodAnalysisConfirmationRequest;
use App\Http\Requests\FoodAnalysisRequest;
use App\Http\Resources\FoodAnalysisResource;
use App\Http\Resources\MealResource;
use App\Models\FoodAnalysis;
use App\Services\Audit\AuditLogService;
use App\Services\Nutrition\FoodAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodAnalysisController extends Controller
{
    public function store(
        FoodAnalysisRequest $request,
        FoodAnalysisService $service,
        AuditLogService $audit,
    ): JsonResponse {
        try {
            $analysis = $service->analyze($request->user(), $request->file('image'));
        } catch (FoodAnalysisException) {
            return response()->json(['message' => __('healo.food_analysis_failed')], 503);
        }
        $audit->record($request->user(), 'food_analysis.api_created', $analysis, request: $request);

        return (new FoodAnalysisResource($analysis))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $foodAnalysis): FoodAnalysisResource
    {
        $analysis = $this->ownedAnalysis($request, $foodAnalysis);
        $this->authorize('view', $analysis);

        return new FoodAnalysisResource($analysis);
    }

    public function confirm(
        FoodAnalysisConfirmationRequest $request,
        int $foodAnalysis,
        FoodAnalysisService $service,
        AuditLogService $audit,
    ): JsonResponse {
        $analysis = $this->ownedAnalysis($request, $foodAnalysis);
        $this->authorize('update', $analysis);
        try {
            $meal = $service->confirm($analysis, $request->validated());
        } catch (FoodAnalysisException) {
            return response()->json(['message' => __('healo.food_analysis_not_reviewable')], 409);
        }
        $audit->record($request->user(), 'food_analysis.api_confirmed', $analysis, request: $request);

        return (new MealResource($meal))->response()->setStatusCode(201);
    }

    public function destroy(
        Request $request,
        int $foodAnalysis,
        FoodAnalysisService $service,
        AuditLogService $audit,
    ): JsonResponse {
        $analysis = $this->ownedAnalysis($request, $foodAnalysis);
        $this->authorize('delete', $analysis);
        $audit->record($request->user(), 'food_analysis.api_cancelled', $analysis, request: $request);
        $service->cancel($analysis);

        return response()->json(status: 204);
    }

    private function ownedAnalysis(Request $request, int $id): FoodAnalysis
    {
        return $request->user()->foodAnalyses()->findOrFail($id);
    }
}
