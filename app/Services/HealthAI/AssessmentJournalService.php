<?php

namespace App\Services\HealthAI;

use App\Models\HealthJournal;
use App\Models\SymptomAssessment;
use App\Services\Journal\HealthJournalService;
use Illuminate\Support\Facades\DB;

class AssessmentJournalService
{
    public function __construct(private readonly HealthJournalService $journals) {}

    public function save(SymptomAssessment $assessment, ?string $title = null): HealthJournal
    {
        if ($assessment->journal) {
            return $assessment->journal;
        }

        return DB::transaction(function () use ($assessment, $title): HealthJournal {
            $journal = $this->journals->create($assessment->user, [
                'title' => $title ?: __('health_ai.journal_title'),
                'journal_date' => today()->toDateString(),
                'mood' => null,
                'complaint' => $assessment->input,
                'symptoms' => collect($assessment->symptoms)->pluck('name')->all(),
                'notes' => $assessment->summary."\n\n".$assessment->recommendation,
            ]);

            $assessment->update([
                'health_journal_id' => $journal->id,
                'saved_to_journal_at' => now(),
            ]);

            return $journal;
        });
    }
}
