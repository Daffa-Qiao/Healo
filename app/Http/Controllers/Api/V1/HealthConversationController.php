<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthConversationRequest;
use App\Http\Resources\HealthConversationResource;
use App\Models\HealthConversation;
use App\Services\Audit\AuditLogService;
use App\Services\HealthAI\HealthAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HealthConversationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return HealthConversationResource::collection(
            $request->user()->healthConversations()->withCount('messages')->latest('last_message_at')->paginate(20)
        );
    }

    public function store(HealthConversationRequest $request, HealthAssistantService $service): JsonResponse
    {
        return (new HealthConversationResource(
            $service->createConversation($request->user(), $request->validated('title'))
        ))->response()->setStatusCode(201);
    }

    public function show(Request $request, HealthConversation $conversation): HealthConversationResource
    {
        $this->authorize('view', $conversation);
        $conversation->load(['messages' => fn ($query) => $query->where('role', '!=', 'system')->reorder()->latest('id')->limit(50)]);
        $conversation->setRelation('messages', $conversation->messages->sortBy('id')->values());

        return new HealthConversationResource($conversation);
    }

    public function destroy(Request $request, HealthConversation $conversation, HealthAssistantService $service, AuditLogService $audit): JsonResponse
    {
        $this->authorize('delete', $conversation);
        $audit->record($request->user(), 'health_conversation.api_deleted', $conversation, request: $request);
        $service->delete($conversation);

        return response()->json(status: 204);
    }
}
