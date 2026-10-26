<?php

namespace App\Policies;

use App\Models\HealthProfile;
use App\Models\User;

class HealthProfilePolicy
{
    public function view(User $user, HealthProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function update(User $user, HealthProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }
}
