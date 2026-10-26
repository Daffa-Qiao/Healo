<?php

namespace Tests\Unit;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\NutritionGoal;
use App\Models\HealthProfile;
use App\Services\Nutrition\CalorieTargetService;
use DomainException;
use Tests\TestCase;

class CalorieTargetServiceTest extends TestCase
{
    public function test_it_calculates_a_bounded_mifflin_st_jeor_target(): void
    {
        $profile = new HealthProfile([
            'height_cm' => 165,
            'weight_kg' => 60,
            'birth_date' => now()->subYears(30)->toDateString(),
            'gender' => Gender::Female,
        ]);

        $result = (new CalorieTargetService)->calculate(
            $profile,
            ActivityLevel::Moderate,
            NutritionGoal::Maintain,
        );

        $this->assertSame(2046, $result->dailyCalories);
        $this->assertSame(1.55, $result->activityFactor);
        $this->assertSame(0, $result->goalAdjustment);
        $this->assertFalse($result->wasClamped);
    }

    public function test_goal_adjustment_and_safety_floor_are_applied(): void
    {
        $profile = new HealthProfile([
            'height_cm' => 145,
            'weight_kg' => 40,
            'birth_date' => now()->subYears(70)->toDateString(),
            'gender' => Gender::Female,
        ]);

        $result = (new CalorieTargetService)->calculate(
            $profile,
            ActivityLevel::Sedentary,
            NutritionGoal::LoseWeight,
        );

        $this->assertSame(1200, $result->dailyCalories);
        $this->assertTrue($result->wasClamped);
    }

    public function test_incomplete_health_profile_is_rejected(): void
    {
        $this->expectException(DomainException::class);

        (new CalorieTargetService)->calculate(
            new HealthProfile(['height_cm' => 165]),
            ActivityLevel::Moderate,
            NutritionGoal::Maintain,
        );
    }
}
