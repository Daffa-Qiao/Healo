<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveAssessmentToJournalRequest;
use App\Http\Requests\SymptomAssessmentRequest;
use App\Http\Resources\SymptomAssessmentResource;
use App\Models\SymptomAssessment;
use App\Services\Audit\AuditLogService;
use App\Services\HealthAI\AssessmentJournalService;
use App\Services\HealthAI\SymptomAssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SymptomAssessmentController extends Controller
{
    public function store(SymptomAssessmentRequest $request, SymptomAssessmentService $service, AuditLogService $audit): JsonResponse|SymptomAssessmentResource
    {
        $data = $request->validated();
        $conversation = isset($data['conversation_id'])
            ? $request->user()->healthConversations()->findOrFail($data['conversation_id'])
            : null;
        $result = $service->assess($request->user(), $data['symptoms'], (bool) ($data['save'] ?? false), $conversation);
        $audit->record($request->user(), 'symptom_assessment.api_processed', $result instanceof SymptomAssessment ? $result : null, request: $request);

        if ($result instanceof SymptomAssessment) {
            return new SymptomAssessmentResource($result);
        }

        return response()->json(['data' => $result->toArray()]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return SymptomAssessmentResource::collection(
            $request->user()->symptomAssessments()->latest()->paginate(20)
        );
    }

    public function show(Request $request, SymptomAssessment $assessment): SymptomAssessmentResource
    {
        $this->authorize('view', $assessment);

        return new SymptomAssessmentResource($assessment);
    }

    public function destroy(Request $request, SymptomAssessment $assessment, AuditLogService $audit): JsonResponse
    {
        $this->authorize('delete', $assessment);
        $audit->record($request->user(), 'symptom_assessment.api_deleted', $assessment, request: $request);
        $assessment->delete();

        return response()->json(status: 204);
    }

    public function saveToJournal(SaveAssessmentToJournalRequest $request, SymptomAssessment $assessment, AssessmentJournalService $service): JsonResponse
    {
        $this->authorize('update', $assessment);
        $journal = $service->save($assessment, $request->validated('title'));

        return response()->json(['data' => ['journal_id' => $journal->id]], 201);
    }
}
