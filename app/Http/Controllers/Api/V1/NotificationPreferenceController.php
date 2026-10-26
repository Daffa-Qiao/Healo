<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNotificationPreferenceRequest;
use App\Http\Resources\NotificationPreferenceResource;
use App\Services\Notification\NotificationPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function show(Request $request, NotificationPreferenceService $preferences): JsonResponse
    {
        return (new NotificationPreferenceResource($preferences->forUser($request->user())))
            ->response()->setStatusCode(200);
    }

    public function update(UpdateNotificationPreferenceRequest $request, NotificationPreferenceService $preferences): JsonResponse
    {
        return (new NotificationPreferenceResource($preferences->update($request->user(), $request->validated())))
            ->response()->setStatusCode(200);
    }
}
