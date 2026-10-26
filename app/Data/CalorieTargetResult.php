<?php

namespace App\Data;

readonly class CalorieTargetResult
{
    public function __construct(
        public int $dailyCalories,
        public float $basalMetabolicRate,
        public float $activityFactor,
        public int $goalAdjustment,
        public bool $wasClamped,
    ) {}
}
