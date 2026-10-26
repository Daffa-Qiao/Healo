<?php

namespace App\Policies;

use App\Models\HealthPlan;
use App\Models\User;

class HealthPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, HealthPlan $plan): bool
    {
        return $plan->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, HealthPlan $plan): bool
    {
        return $this->view($user, $plan);
    }

    public function delete(User $user, HealthPlan $plan): bool
    {
        return $this->view($user, $plan);
    }
}
