<?php

namespace App\Policies;

use App\Models\NutritionDailySummary;
use App\Models\User;

class NutritionDailySummaryPolicy
{
    public function view(User $user, NutritionDailySummary $summary): bool
    {
        return $user->id === $summary->user_id;
    }
}
