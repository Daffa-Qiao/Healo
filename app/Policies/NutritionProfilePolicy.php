<?php

namespace App\Policies;

use App\Models\NutritionProfile;
use App\Models\User;

class NutritionProfilePolicy
{
    public function view(User $user, NutritionProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function update(User $user, NutritionProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }
}
