<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthJournalRequest;
use App\Http\Resources\HealthJournalResource;
use App\Models\HealthJournal;
use App\Services\Audit\AuditLogService;
use App\Services\Journal\HealthJournalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HealthJournalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return HealthJournalResource::collection(
            $request->user()->healthJournals()->with('entries')->latest('journal_date')->paginate(20)
        );
    }

    public function store(
        HealthJournalRequest $request,
        HealthJournalService $service,
        AuditLogService $audit,
    ): JsonResponse {
        $journal = $service->create($request->user(), $request->validated());
        $audit->record($request->user(), 'health_journal.api_created', $journal, request: $request);

        return (new HealthJournalResource($journal))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $healthJournal): HealthJournalResource
    {
        $journal = $this->ownedJournal($request, $healthJournal);
        $this->authorize('view', $journal);

        return new HealthJournalResource($journal);
    }

    public function update(
        HealthJournalRequest $request,
        int $healthJournal,
        HealthJournalService $service,
        AuditLogService $audit,
    ): HealthJournalResource {
        $journal = $this->ownedJournal($request, $healthJournal);
        $this->authorize('update', $journal);
        $journal = $service->update($journal, $request->validated());
        $audit->record($request->user(), 'health_journal.api_updated', $journal, request: $request);

        return new HealthJournalResource($journal);
    }

    public function destroy(Request $request, int $healthJournal, AuditLogService $audit): JsonResponse
    {
        $journal = $this->ownedJournal($request, $healthJournal);
        $this->authorize('delete', $journal);
        $audit->record($request->user(), 'health_journal.api_deleted', $journal, request: $request);
        $journal->delete();

        return response()->json(status: 204);
    }

    private function ownedJournal(Request $request, int $id): HealthJournal
    {
        return $request->user()->healthJournals()->with('entries')->findOrFail($id);
    }
}
