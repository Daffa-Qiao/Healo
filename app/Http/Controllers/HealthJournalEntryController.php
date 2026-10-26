<?php

namespace App\Http\Controllers;

use App\Http\Requests\HealthJournalEntryRequest;
use App\Models\HealthJournalEntry;
use App\Services\Audit\AuditLogService;
use App\Services\Journal\HealthJournalEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HealthJournalEntryController extends Controller
{
    public function store(
        HealthJournalEntryRequest $request,
        int $healthJournal,
        HealthJournalEntryService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $journal = $request->user()->healthJournals()->findOrFail($healthJournal);
        $this->authorize('update', $journal);
        $entry = $service->create($journal, $request->validated());
        $audit->record($request->user(), 'health_journal_entry.created', $entry, request: $request);

        return redirect()->route('health-journals.show', $journal)->with('status', 'entry-created');
    }

    public function update(
        HealthJournalEntryRequest $request,
        int $healthJournalEntry,
        HealthJournalEntryService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $entry = $this->ownedEntry($request, $healthJournalEntry);
        $this->authorize('update', $entry);
        $entry = $service->update($entry, $request->validated());
        $audit->record($request->user(), 'health_journal_entry.updated', $entry, request: $request);

        return redirect()->route('health-journals.show', $entry->health_journal_id)->with('status', 'entry-updated');
    }

    public function destroy(
        Request $request,
        int $healthJournalEntry,
        AuditLogService $audit,
    ): RedirectResponse {
        $entry = $this->ownedEntry($request, $healthJournalEntry);
        $this->authorize('delete', $entry);
        $journalId = $entry->health_journal_id;
        $audit->record($request->user(), 'health_journal_entry.deleted', $entry, request: $request);
        $entry->delete();

        return redirect()->route('health-journals.show', $journalId)->with('status', 'entry-deleted');
    }

    private function ownedEntry(Request $request, int $id): HealthJournalEntry
    {
        return $request->user()->healthJournalEntries()->findOrFail($id);
    }
}
