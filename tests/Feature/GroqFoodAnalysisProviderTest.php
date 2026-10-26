<?php

namespace Tests\Feature;

use App\Contracts\FoodAnalysisProviderInterface;
use App\Services\Nutrition\GroqFoodAnalysisProvider;
use App\Services\Nutrition\LocalDemoFoodAnalysisProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqFoodAnalysisProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('nutrition.food_analysis_provider', 'groq');
        config()->set('services.groq.api_key', 'groq-food-secret');
        config()->set('services.groq.base_url', 'https://api.groq.com/openai/v1');
        config()->set('services.groq.vision_model', 'qwen/qwen3.8-27b');
    }

    public function test_groq_food_provider_sends_private_image_with_strict_schema_and_parses_result(): void
    {
        Http::fake(['api.groq.com/*' => Http::response([
            'choices' => [['message' => ['content' => json_encode([
                'foods' => [[
                    'name' => 'Nasi kuning',
                    'serving_size' => 180,
                    'serving_unit' => 'g',
                    'calories' => 270,
                    'protein_g' => 5,
                    'carbohydrates_g' => 52,
                    'fat_g' => 5,
                ]],
                'confidence' => 0.87,
            ])]]],
        ])]);

        $path = $this->temporaryPng();

        try {
            $result = app(GroqFoodAnalysisProvider::class)->analyze($path, 'meal.png');
        } finally {
            @unlink($path);
        }

        $this->assertSame('Nasi kuning', $result->foods[0]->name);
        $this->assertSame(270.0, $result->foods[0]->calories);
        $this->assertSame(0.87, $result->confidence);
        $this->assertSame('groq_vision', $result->provider);

        Http::assertSent(function (Request $request): bool {
            $this->assertSame('qwen/qwen3.8-27b', $request['model']);
            $this->assertSame('json_schema', $request['response_format']['type']);
            $this->assertTrue($request['response_format']['json_schema']['strict']);
            $this->assertFalse($request['store']);
            $this->assertStringStartsWith('data:image/png;base64,', $request['messages'][1]['content'][1]['image_url']['url']);

            return $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
                && $request->hasHeader('Authorization', 'Bearer groq-food-secret');
        });
    }

    public function test_food_provider_binding_falls_back_locally_without_api_key(): void
    {
        $this->assertInstanceOf(GroqFoodAnalysisProvider::class, app(FoodAnalysisProviderInterface::class));

        config()->set('services.groq.api_key');

        $this->assertInstanceOf(LocalDemoFoodAnalysisProvider::class, app(FoodAnalysisProviderInterface::class));
    }

    private function temporaryPng(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'healo-food-');
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Z3S8AAAAASUVORK5CYII=',
            true,
        ));

        return $path;
    }
}
