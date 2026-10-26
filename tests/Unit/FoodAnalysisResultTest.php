<?php

namespace Tests\Unit;

use App\Data\FoodAnalysisItem;
use App\Data\FoodAnalysisResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FoodAnalysisResultTest extends TestCase
{
    public function test_it_transforms_items_into_a_stable_structured_payload(): void
    {
        $result = new FoodAnalysisResult([
            new FoodAnalysisItem('Nasi putih', 150, 'g', 195, 4, 42, 0.5),
            new FoodAnalysisItem('Ayam', 100, 'g', 220, 28, 2, 11),
        ], 0.82, 'fake');

        $payload = $result->toArray();

        $this->assertSame(415.0, $payload['total_estimated_calories']);
        $this->assertSame('Nasi putih', $payload['foods'][0]['name']);
        $this->assertSame(0.82, $payload['confidence']);
        $this->assertSame('fake', $payload['provider']);
    }

    public function test_it_rejects_invalid_provider_results(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FoodAnalysisResult([], 1.2, 'fake');
    }
}
