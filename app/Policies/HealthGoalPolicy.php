<?php

namespace App\Policies;

use App\Models\HealthGoal;
use App\Models\User;

class HealthGoalPolicy
{
    public function view(User $user, HealthGoal $goal): bool
    {
        return $user->id === $goal->user_id;
    }

    public function update(User $user, HealthGoal $goal): bool
    {
        return $user->id === $goal->user_id;
    }

    public function delete(User $user, HealthGoal $goal): bool
    {
        return $user->id === $goal->user_id;
    }
}
