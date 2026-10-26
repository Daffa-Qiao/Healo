<?php

namespace App\Policies;

use App\Models\FoodAnalysis;
use App\Models\User;

class FoodAnalysisPolicy
{
    public function view(User $user, FoodAnalysis $analysis): bool
    {
        return $user->id === $analysis->user_id;
    }

    public function update(User $user, FoodAnalysis $analysis): bool
    {
        return $user->id === $analysis->user_id;
    }

    public function delete(User $user, FoodAnalysis $analysis): bool
    {
        return $user->id === $analysis->user_id;
    }
}
