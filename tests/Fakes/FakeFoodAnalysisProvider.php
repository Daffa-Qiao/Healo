<?php

namespace Tests\Fakes;

use App\Contracts\FoodAnalysisProviderInterface;
use App\Data\FoodAnalysisItem;
use App\Data\FoodAnalysisResult;
use RuntimeException;

class FakeFoodAnalysisProvider implements FoodAnalysisProviderInterface
{
    public int $calls = 0;

    public bool $shouldFail = false;

    public float $confidence = 0.82;

    public function analyze(string $imagePath, string $originalName): FoodAnalysisResult
    {
        $this->calls++;
        if ($this->shouldFail) {
            throw new RuntimeException('Simulated provider timeout.');
        }

        return new FoodAnalysisResult([
            new FoodAnalysisItem('Nasi putih', 150, 'g', 195, 4, 42, 0.5),
            new FoodAnalysisItem('Ayam panggang', 100, 'g', 220, 28, 2, 11),
        ], $this->confidence, 'fake');
    }
}
