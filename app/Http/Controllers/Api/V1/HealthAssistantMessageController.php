<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthMessageRequest;
use App\Http\Resources\HealthMessageResource;
use App\Models\HealthConversation;
use App\Services\Audit\AuditLogService;
use App\Services\HealthAI\HealthAssistantService;
use Illuminate\Http\JsonResponse;

class HealthAssistantMessageController extends Controller
{
    public function store(HealthMessageRequest $request, HealthConversation $conversation, HealthAssistantService $service, AuditLogService $audit): JsonResponse
    {
        $this->authorize('update', $conversation);
        $message = $service->send($conversation, $request->validated('message'));
        $audit->record($request->user(), 'health_assistant.api_message_processed', $conversation, request: $request);

        return (new HealthMessageResource($message))->response()->setStatusCode(201);
    }
}
