<?php

namespace App\Policies;

use App\Models\HealthJournalEntry;
use App\Models\User;

class HealthJournalEntryPolicy
{
    public function view(User $user, HealthJournalEntry $entry): bool
    {
        return $user->id === $entry->journal->user_id;
    }

    public function update(User $user, HealthJournalEntry $entry): bool
    {
        return $user->id === $entry->journal->user_id;
    }

    public function delete(User $user, HealthJournalEntry $entry): bool
    {
        return $user->id === $entry->journal->user_id;
    }
}
