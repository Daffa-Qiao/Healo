<?php

namespace App\Http\Controllers;

use App\Enums\JournalEntryType;
use App\Enums\Mood;
use App\Http\Requests\HealthJournalRequest;
use App\Models\HealthJournal;
use App\Services\Audit\AuditLogService;
use App\Services\Journal\HealthJournalDashboardService;
use App\Services\Journal\HealthJournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HealthJournalController extends Controller
{
    public function index(Request $request, HealthJournalDashboardService $dashboardService): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'mood' => ['nullable', Rule::enum(Mood::class)],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $journals = $request->user()->healthJournals()
            ->with('entries')
            ->when($validated['q'] ?? null, fn ($query, $search) => $query->where('title', 'like', '%'.$search.'%'))
            ->when($validated['mood'] ?? null, fn ($query, $mood) => $query->where('mood', $mood))
            ->when($validated['month'] ?? null, function ($query, $month) {
                $query->whereYear('journal_date', substr($month, 0, 4))
                    ->whereMonth('journal_date', substr($month, 5, 2));
            })
            ->latest('journal_date')
            ->paginate(9)
            ->withQueryString();

        return view('health-journals.dashboard', [
            'journals' => $journals,
            'moods' => Mood::cases(),
            'dashboard' => $dashboardService->build($request->user()),
        ]);
    }

    public function create(): View
    {
        return view('health-journals.create', ['moods' => Mood::cases()]);
    }

    public function store(
        HealthJournalRequest $request,
        HealthJournalService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $journal = $service->create($request->user(), $request->validated());
        $audit->record($request->user(), 'health_journal.created', $journal, request: $request);

        return redirect()->route('health-journals.show', $journal)->with('status', 'journal-created');
    }

    public function show(Request $request, int $healthJournal): View
    {
        $journal = $this->ownedJournal($request, $healthJournal);
        $this->authorize('view', $journal);

        return view('health-journals.show', [
            'journal' => $journal->load('entries'),
            'entryTypes' => JournalEntryType::cases(),
        ]);
    }

    public function edit(Request $request, int $healthJournal): View
    {
        $journal = $this->ownedJournal($request, $healthJournal);
        $this->authorize('update', $journal);

        return view('health-journals.edit', [
            'journal' => $journal->load('entries'),
            'moods' => Mood::cases(),
        ]);
    }

    public function update(
        HealthJournalRequest $request,
        int $healthJournal,
        HealthJournalService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $journal = $this->ownedJournal($request, $healthJournal);
        $this->authorize('update', $journal);
        $journal = $service->update($journal, $request->validated());
        $audit->record($request->user(), 'health_journal.updated', $journal, request: $request);

        return redirect()->route('health-journals.show', $journal)->with('status', 'journal-updated');
    }

    public function destroy(Request $request, int $healthJournal, AuditLogService $audit): RedirectResponse
    {
        $journal = $this->ownedJournal($request, $healthJournal);
        $this->authorize('delete', $journal);
        $audit->record($request->user(), 'health_journal.deleted', $journal, request: $request);
        $journal->delete();

        return redirect()->route('health-journals.index')->with('status', 'journal-deleted');
    }

    private function ownedJournal(Request $request, int $id): HealthJournal
    {
        return $request->user()->healthJournals()->with('entries')->findOrFail($id);
    }
}
