<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\HealthJournalEntryRequest;
use App\Http\Resources\HealthJournalEntryResource;
use App\Models\HealthJournalEntry;
use App\Services\Audit\AuditLogService;
use App\Services\Journal\HealthJournalEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthJournalEntryController extends Controller
{
    public function store(
        HealthJournalEntryRequest $request,
        int $healthJournal,
        HealthJournalEntryService $service,
        AuditLogService $audit,
    ): JsonResponse {
        $journal = $request->user()->healthJournals()->findOrFail($healthJournal);
        $this->authorize('update', $journal);
        $entry = $service->create($journal, $request->validated());
        $audit->record($request->user(), 'health_journal_entry.api_created', $entry, request: $request);

        return (new HealthJournalEntryResource($entry))->response()->setStatusCode(201);
    }

    public function update(
        HealthJournalEntryRequest $request,
        int $healthJournalEntry,
        HealthJournalEntryService $service,
        AuditLogService $audit,
    ): HealthJournalEntryResource {
        $entry = $this->ownedEntry($request, $healthJournalEntry);
        $this->authorize('update', $entry);
        $entry = $service->update($entry, $request->validated());
        $audit->record($request->user(), 'health_journal_entry.api_updated', $entry, request: $request);

        return new HealthJournalEntryResource($entry);
    }

    public function destroy(Request $request, int $healthJournalEntry, AuditLogService $audit): JsonResponse
    {
        $entry = $request->user()->healthJournalEntries()->findOrFail($healthJournalEntry);
        $this->authorize('delete', $entry);
        $audit->record($request->user(), 'health_journal_entry.api_deleted', $entry, request: $request);
        $entry->delete();

        return response()->json(status: 204);
    }

    private function ownedEntry(HealthJournalEntryRequest $request, int $id): HealthJournalEntry
    {
        return $request->user()->healthJournalEntries()->findOrFail($id);
    }
}
