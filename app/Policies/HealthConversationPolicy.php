<?php

namespace App\Policies;

use App\Models\HealthConversation;
use App\Models\User;

class HealthConversationPolicy
{
    public function view(User $user, HealthConversation $conversation): bool
    {
        return $user->id === $conversation->user_id;
    }

    public function update(User $user, HealthConversation $conversation): bool
    {
        return $user->id === $conversation->user_id;
    }

    public function delete(User $user, HealthConversation $conversation): bool
    {
        return $user->id === $conversation->user_id;
    }
}
