<?php

namespace App\Services\Nutrition;

use App\Contracts\FoodAnalysisProviderInterface;
use App\Data\FoodAnalysisItem;
use App\Data\FoodAnalysisResult;

class LocalDemoFoodAnalysisProvider implements FoodAnalysisProviderInterface
{
    public function analyze(string $imagePath, string $originalName): FoodAnalysisResult
    {
        if (! is_file($imagePath) || filesize($imagePath) === 0) {
            throw new \RuntimeException('The uploaded image cannot be read.');
        }

        $name = mb_strtolower($originalName);
        $foods = str_contains($name, 'nasi') || str_contains($name, 'rice')
            ? [
                new FoodAnalysisItem('Nasi putih (estimasi demo)', 150, 'g', 195, 4, 42, 0.5),
                new FoodAnalysisItem('Lauk pada foto (perlu verifikasi)', 100, 'g', 220, 18, 8, 12),
            ]
            : [
                new FoodAnalysisItem('Makanan pada foto (perlu verifikasi)', 100, 'g', 200),
            ];

        return new FoodAnalysisResult($foods, 0.32, 'local_demo');
    }
}
