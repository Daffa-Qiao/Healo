<?php

namespace App\Services\Nutrition;

use App\Enums\ActivityLevel;
use App\Enums\NutritionGoal;
use App\Models\NutritionProfile;
use App\Models\User;
use Illuminate\Support\Arr;

class NutritionProfileService
{
    public function __construct(
        private readonly CalorieTargetService $calculator,
        private readonly NutritionSummaryService $summaries,
    ) {}

    /** @param array<string, mixed> $data */
    public function update(User $user, array $data): NutritionProfile
    {
        $profile = $user->nutritionProfile()->firstOrNew();
        $profile->fill(Arr::only($data, ['activity_level', 'goal', 'diet_preference', 'food_preferences']));

        if (($data['recalculate'] ?? false) || (! $profile->exists && ! isset($data['daily_calorie_target']))) {
            $healthProfile = $user->healthProfile()->first();
            if (! $healthProfile) {
                throw new \DomainException('Complete your Health Profile before calculating a calorie target.');
            }
            $result = $this->calculator->calculate(
                $healthProfile,
                ActivityLevel::from($data['activity_level']),
                NutritionGoal::from($data['goal']),
            );
            $profile->daily_calorie_target = $result->dailyCalories;
            $profile->target_source = 'calculated';
            $profile->calculated_at = now();
        } elseif (isset($data['daily_calorie_target'])) {
            $profile->daily_calorie_target = $data['daily_calorie_target'];
            $profile->target_source = 'manual';
            $profile->calculated_at = null;
        }

        $profile->save();
        $this->summaries->refresh($user, today());

        return $profile;
    }
}
