<?php

namespace App\Http\Controllers;

use App\Http\Requests\HealthMessageRequest;
use App\Models\HealthConversation;
use App\Services\Audit\AuditLogService;
use App\Services\HealthAI\HealthAssistantService;
use Illuminate\Http\RedirectResponse;

class HealthAssistantMessageController extends Controller
{
    public function store(HealthMessageRequest $request, HealthConversation $conversation, HealthAssistantService $service, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('update', $conversation);
        $service->send($conversation, $request->validated('message'));
        $audit->record($request->user(), 'health_assistant.message_processed', $conversation, request: $request);

        return redirect()->route('health-assistant.show', $conversation);
    }
}
