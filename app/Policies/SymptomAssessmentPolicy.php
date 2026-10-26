<?php

namespace App\Policies;

use App\Models\SymptomAssessment;
use App\Models\User;

class SymptomAssessmentPolicy
{
    public function view(User $user, SymptomAssessment $assessment): bool
    {
        return $user->id === $assessment->user_id;
    }

    public function update(User $user, SymptomAssessment $assessment): bool
    {
        return $user->id === $assessment->user_id;
    }

    public function delete(User $user, SymptomAssessment $assessment): bool
    {
        return $user->id === $assessment->user_id;
    }
}
