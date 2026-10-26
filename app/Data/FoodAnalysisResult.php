<?php

namespace App\Data;

use InvalidArgumentException;

readonly class FoodAnalysisResult
{
    /** @param list<FoodAnalysisItem> $foods */
    public function __construct(
        public array $foods,
        public float $confidence,
        public string $provider,
    ) {
        if ($foods === [] || $confidence < 0 || $confidence > 1) {
            throw new InvalidArgumentException('Food analysis result is invalid.');
        }
    }

    public function totalCalories(): float
    {
        return round(array_sum(array_map(fn (FoodAnalysisItem $food): float => $food->calories, $this->foods)), 2);
    }

    /** @return array{foods: list<array<string, float|string>>, total_estimated_calories: float, confidence: float, provider: string} */
    public function toArray(): array
    {
        return [
            'foods' => array_map(fn (FoodAnalysisItem $food): array => $food->toArray(), $this->foods),
            'total_estimated_calories' => $this->totalCalories(),
            'confidence' => $this->confidence,
            'provider' => $this->provider,
        ];
    }
}
