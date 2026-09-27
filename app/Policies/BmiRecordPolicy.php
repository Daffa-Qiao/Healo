<?php

namespace App\Policies;

use App\Models\BmiRecord;
use App\Models\User;

class BmiRecordPolicy
{
    public function view(User $user, BmiRecord $record): bool
    {
        return $user->id === $record->user_id;
    }

    public function delete(User $user, BmiRecord $record): bool
    {
        return $user->id === $record->user_id;
    }
}

