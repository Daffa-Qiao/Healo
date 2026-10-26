<?php

namespace App\Data;

readonly class FoodAnalysisItem
{
    public function __construct(
        public string $name,
        public float $servingSize,
        public string $servingUnit,
        public float $calories,
        public float $proteinG = 0,
        public float $carbohydratesG = 0,
        public float $fatG = 0,
    ) {}

    /** @return array<string, float|string> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'serving_size' => $this->servingSize,
            'serving_unit' => $this->servingUnit,
            'calories' => $this->calories,
            'protein_g' => $this->proteinG,
            'carbohydrates_g' => $this->carbohydratesG,
            'fat_g' => $this->fatG,
        ];
    }
}
