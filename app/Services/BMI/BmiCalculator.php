<?php

namespace App\Services\BMI;

use App\Data\BmiResult;
use App\Enums\BmiCategory;
use InvalidArgumentException;

class BmiCalculator
{
    public function calculate(float $weightKg, float $heightCm): BmiResult
    {
        if ($weightKg <= 0 || $heightCm <= 0) {
            throw new InvalidArgumentException('Weight and height must be greater than zero.');
        }

        $value = round($weightKg / (($heightCm / 100) ** 2), 2);

        $category = match (true) {
            $value < 18.5 => BmiCategory::Underweight,
            $value < 25 => BmiCategory::Healthy,
            $value < 30 => BmiCategory::Overweight,
            default => BmiCategory::Obesity,
        };

        return new BmiResult($value, $category);
    }
}

