<?php

namespace Tests\Unit;

use App\Enums\BmiCategory;
use App\Services\BMI\BmiCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BmiCalculatorTest extends TestCase
{
    public function test_it_calculates_and_rounds_bmi(): void
    {
        $result = (new BmiCalculator)->calculate(70, 175);

        $this->assertSame(22.86, $result->value);
        $this->assertSame(BmiCategory::Healthy, $result->category);
    }

    public function test_it_classifies_all_bmi_ranges(): void
    {
        $calculator = new BmiCalculator;

        $this->assertSame(BmiCategory::Underweight, $calculator->calculate(50, 175)->category);
        $this->assertSame(BmiCategory::Healthy, $calculator->calculate(65, 175)->category);
        $this->assertSame(BmiCategory::Overweight, $calculator->calculate(80, 175)->category);
        $this->assertSame(BmiCategory::Obesity, $calculator->calculate(100, 175)->category);
    }

    public function test_it_rejects_non_positive_measurements(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BmiCalculator)->calculate(70, 0);
    }
}
