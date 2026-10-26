<?php

namespace App\Policies;

use App\Models\MealItem;
use App\Models\User;

class MealItemPolicy
{
    public function view(User $user, MealItem $item): bool
    {
        return $user->id === $item->meal->user_id;
    }

    public function update(User $user, MealItem $item): bool
    {
        return $user->id === $item->meal->user_id;
    }

    public function delete(User $user, MealItem $item): bool
    {
        return $user->id === $item->meal->user_id;
    }
}
