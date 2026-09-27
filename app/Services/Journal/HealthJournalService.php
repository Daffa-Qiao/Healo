<?php

namespace App\Services\Journal;

use App\Enums\JournalEntryType;
use App\Models\HealthJournal;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class HealthJournalService
{
    /** @param array<string, mixed> $data */
    public function create(User $user, array $data): HealthJournal
    {
        return DB::transaction(function () use ($user, $data): HealthJournal {
            $journal = $user->healthJournals()->create(Arr::only($data, ['title', 'journal_date', 'mood']));
            $this->syncEntries($journal, $data);

            return $journal->load('entries');
        });
    }

    /** @param array<string, mixed> $data */
    public function update(HealthJournal $journal, array $data): HealthJournal
    {
        return DB::transaction(function () use ($journal, $data): HealthJournal {
            $journal->update(Arr::only($data, ['title', 'journal_date', 'mood']));
            $journal->entries()->delete();
            $this->syncEntries($journal, $data);

            return $journal->load('entries');
        });
    }

    /** @param array<string, mixed> $data */
    private function syncEntries(HealthJournal $journal, array $data): void
    {
        $entries = [];
        $sort = 0;

        if (! empty($data['complaint'])) {
            $entries[] = ['type' => JournalEntryType::Complaint, 'content' => $data['complaint'], 'sort_order' => $sort++];
        }

        foreach ($data['symptoms'] ?? [] as $symptom) {
            $entries[] = ['type' => JournalEntryType::Symptom, 'content' => $symptom, 'sort_order' => $sort++];
        }

        if (! empty($data['notes'])) {
            $entries[] = ['type' => JournalEntryType::Note, 'content' => $data['notes'], 'sort_order' => $sort];
        }

        $journal->entries()->createMany($entries);
    }
}

