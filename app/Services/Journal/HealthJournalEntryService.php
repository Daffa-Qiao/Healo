<?php

namespace App\Services\Journal;

use App\Models\HealthJournal;
use App\Models\HealthJournalEntry;

class HealthJournalEntryService
{
    /** @param array{type: string, content: string} $data */
    public function create(HealthJournal $journal, array $data): HealthJournalEntry
    {
        $nextOrder = ((int) $journal->entries()->max('sort_order')) + 1;

        return $journal->entries()->create([
            ...$data,
            'sort_order' => $nextOrder,
        ]);
    }

    /** @param array{type: string, content: string} $data */
    public function update(HealthJournalEntry $entry, array $data): HealthJournalEntry
    {
        $entry->update($data);

        return $entry->refresh();
    }
}
