<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\NutritionProfileRequest;
use App\Http\Resources\NutritionProfileResource;
use App\Services\Audit\AuditLogService;
use App\Services\Nutrition\NutritionProfileService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NutritionProfileController extends Controller
{
    public function show(Request $request): NutritionProfileResource|JsonResponse
    {
        $profile = $request->user()->nutritionProfile()->first();
        if (! $profile) {
            return response()->json(['data' => null]);
        }
        $this->authorize('view', $profile);

        return new NutritionProfileResource($profile);
    }

    public function update(
        NutritionProfileRequest $request,
        NutritionProfileService $service,
        AuditLogService $audit,
    ): JsonResponse {
        $existing = $request->user()->nutritionProfile()->first();
        if ($existing) {
            $this->authorize('update', $existing);
        }

        try {
            $profile = $service->update($request->user(), $request->validated());
        } catch (DomainException $exception) {
            return response()->json(['message' => $exception->getMessage(), 'errors' => ['calorie_target' => [$exception->getMessage()]]], 422);
        }

        $audit->record($request->user(), 'nutrition_profile.api_updated', $profile, request: $request);

        return (new NutritionProfileResource($profile))->response()->setStatusCode(200);
    }
}
