<?php

namespace App\Policies;

use App\Models\HealthPlanTask;
use App\Models\User;

class HealthPlanTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, HealthPlanTask $task): bool
    {
        return $task->healthPlan()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, HealthPlanTask $task): bool
    {
        return $this->view($user, $task);
    }

    public function delete(User $user, HealthPlanTask $task): bool
    {
        return false;
    }

    public function complete(User $user, HealthPlanTask $task): bool
    {
        return $this->view($user, $task);
    }

    public function uncomplete(User $user, HealthPlanTask $task): bool
    {
        return $this->view($user, $task);
    }
}
