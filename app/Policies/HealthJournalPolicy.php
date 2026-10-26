<?php

namespace App\Policies;

use App\Models\HealthJournal;
use App\Models\User;

class HealthJournalPolicy
{
    public function view(User $user, HealthJournal $journal): bool
    {
        return $user->id === $journal->user_id;
    }

    public function update(User $user, HealthJournal $journal): bool
    {
        return $user->id === $journal->user_id;
    }

    public function delete(User $user, HealthJournal $journal): bool
    {
        return $user->id === $journal->user_id;
    }
}
