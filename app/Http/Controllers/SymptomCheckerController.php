<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAssessmentToJournalRequest;
use App\Http\Requests\SymptomAssessmentRequest;
use App\Models\SymptomAssessment;
use App\Services\Audit\AuditLogService;
use App\Services\HealthAI\AssessmentJournalService;
use App\Services\HealthAI\SymptomAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SymptomCheckerController extends Controller
{
    public function create(Request $request): View
    {
        return view('symptom-checker.create', [
            'history' => $request->user()->symptomAssessments()->latest()->limit(5)->get(),
        ]);
    }

    public function store(SymptomAssessmentRequest $request, SymptomAssessmentService $service, AuditLogService $audit): View|RedirectResponse
    {
        $data = $request->validated();
        $conversation = isset($data['conversation_id'])
            ? $request->user()->healthConversations()->findOrFail($data['conversation_id'])
            : null;
        $result = $service->assess(
            $request->user(),
            $data['symptoms'],
            (bool) ($data['save'] ?? false),
            $conversation,
        );
        $audit->record($request->user(), 'symptom_assessment.processed', $result instanceof SymptomAssessment ? $result : null, request: $request);

        if ($result instanceof SymptomAssessment) {
            return redirect()->route('symptom-checker.show', $result);
        }

        return view('symptom-checker.result', [
            'result' => $result,
            'assessment' => null,
        ]);
    }

    public function history(Request $request): View
    {
        return view('symptom-checker.history', [
            'assessments' => $request->user()->symptomAssessments()->latest()->paginate(15),
        ]);
    }

    public function show(Request $request, SymptomAssessment $assessment): View
    {
        $this->authorize('view', $assessment);

        return view('symptom-checker.result', ['result' => $assessment, 'assessment' => $assessment]);
    }

    public function destroy(Request $request, SymptomAssessment $assessment, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('delete', $assessment);
        $audit->record($request->user(), 'symptom_assessment.deleted', $assessment, request: $request);
        $assessment->delete();

        return redirect()->route('symptom-checker.history')->with('status', 'assessment-deleted');
    }

    public function saveToJournal(SaveAssessmentToJournalRequest $request, SymptomAssessment $assessment, AssessmentJournalService $service, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('update', $assessment);
        $journal = $service->save($assessment, $request->validated('title'));
        $audit->record($request->user(), 'symptom_assessment.saved_to_journal', $assessment, request: $request);

        return redirect()->route('health-journals.show', $journal)->with('status', 'assessment-saved-to-journal');
    }
}
