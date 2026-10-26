<?php

namespace App\Services\Nutrition;

use App\Data\CalorieTargetResult;
use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\NutritionGoal;
use App\Models\HealthProfile;
use DomainException;

class CalorieTargetService
{
    public function calculate(
        HealthProfile $profile,
        ActivityLevel $activityLevel,
        NutritionGoal $goal,
    ): CalorieTargetResult {
        if (! $profile->birth_date || ! $profile->gender || ! $profile->height_cm || ! $profile->weight_kg) {
            throw new DomainException('Health profile must include birth date, gender, height, and weight.');
        }

        $age = $profile->birth_date->age;
        if ($age < 18 || $age > 100) {
            throw new DomainException('Calorie estimation is available for adults aged 18 to 100.');
        }

        $genderConstant = match ($profile->gender) {
            Gender::Male => 5,
            Gender::Female => -161,
            Gender::Other, Gender::PreferNotToSay => -78,
        };

        $bmr = (10 * (float) $profile->weight_kg)
            + (6.25 * (float) $profile->height_cm)
            - (5 * $age)
            + $genderConstant;

        $activityFactor = match ($activityLevel) {
            ActivityLevel::Sedentary => 1.2,
            ActivityLevel::Light => 1.375,
            ActivityLevel::Moderate => 1.55,
            ActivityLevel::Active => 1.725,
            ActivityLevel::VeryActive => 1.9,
        };

        $adjustment = match ($goal) {
            NutritionGoal::LoseWeight => -300,
            NutritionGoal::Maintain => 0,
            NutritionGoal::GainWeight => 300,
        };

        $unbounded = (int) round(($bmr * $activityFactor) + $adjustment);
        $minimum = (int) config('nutrition.calorie_target.minimum', 1200);
        $maximum = (int) config('nutrition.calorie_target.maximum', 4500);
        $daily = max($minimum, min($maximum, $unbounded));

        return new CalorieTargetResult(
            $daily,
            round($bmr, 2),
            $activityFactor,
            $adjustment,
            $daily !== $unbounded,
        );
    }
}
