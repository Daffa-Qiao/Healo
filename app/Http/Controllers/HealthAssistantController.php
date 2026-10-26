<?php

namespace App\Http\Controllers;

use App\Http\Requests\HealthConversationRequest;
use App\Models\HealthConversation;
use App\Services\Audit\AuditLogService;
use App\Services\HealthAI\HealthAssistantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthAssistantController extends Controller
{
    public function index(Request $request): View
    {
        return $this->page($request);
    }

    public function store(HealthConversationRequest $request, HealthAssistantService $service): RedirectResponse
    {
        $conversation = $service->createConversation($request->user(), $request->validated('title'));

        return redirect()->route('health-assistant.show', $conversation);
    }

    public function show(Request $request, HealthConversation $conversation): View
    {
        $this->authorize('view', $conversation);

        return $this->page($request, $conversation);
    }

    public function destroy(Request $request, HealthConversation $conversation, HealthAssistantService $service, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('delete', $conversation);
        $audit->record($request->user(), 'health_conversation.deleted', $conversation, request: $request);
        $service->delete($conversation);

        return redirect()->route('health-assistant.index')->with('status', 'conversation-deleted');
    }

    private function page(Request $request, ?HealthConversation $selected = null): View
    {
        $conversations = $request->user()->healthConversations()
            ->withCount('messages')->latest('last_message_at')->latest('id')->paginate(15);

        if ($selected) {
            $selected->load(['messages' => fn ($query) => $query->where('role', '!=', 'system')->reorder()->latest('id')->limit(50)]);
            $selected->setRelation('messages', $selected->messages->sortBy('id')->values());
        }

        return view('health-assistant.index', compact('conversations', 'selected'));
    }
}
