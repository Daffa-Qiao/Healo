<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthProfileRequest;
use App\Http\Resources\HealthProfileResource;
use App\Services\Audit\AuditLogService;
use App\Services\Health\HealthProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthProfileController extends Controller
{
    public function show(Request $request): HealthProfileResource|JsonResponse
    {
        $profile = $request->user()->healthProfile()->first();

        if (! $profile) {
            return response()->json(['data' => null]);
        }

        $this->authorize('view', $profile);

        return new HealthProfileResource($profile);
    }

    public function update(
        HealthProfileRequest $request,
        HealthProfileService $service,
        AuditLogService $audit,
    ): JsonResponse {
        $existing = $request->user()->healthProfile()->first();
        if ($existing) {
            $this->authorize('update', $existing);
        }

        $profile = $service->update($request->user(), $request->validated());
        $audit->record($request->user(), 'health_profile.api_updated', $profile, request: $request);

        return (new HealthProfileResource($profile))->response()->setStatusCode(200);
    }
}
