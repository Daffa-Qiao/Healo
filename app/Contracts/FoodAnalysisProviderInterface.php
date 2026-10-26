<?php

namespace App\Contracts;

use App\Data\FoodAnalysisResult;

interface FoodAnalysisProviderInterface
{
    public function analyze(string $imagePath, string $originalName): FoodAnalysisResult;
}
