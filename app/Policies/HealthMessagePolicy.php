<?php

namespace App\Policies;

use App\Models\HealthMessage;
use App\Models\User;

class HealthMessagePolicy
{
    public function view(User $user, HealthMessage $message): bool
    {
        return $user->id === $message->conversation()->value('user_id');
    }

    public function delete(User $user, HealthMessage $message): bool
    {
        return $this->view($user, $message);
    }
}
