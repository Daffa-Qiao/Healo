<?php

namespace Tests\Feature;

use App\Contracts\FoodAnalysisProviderInterface;
use App\Enums\FoodAnalysisStatus;
use App\Enums\NutritionSource;
use App\Models\User;
use App\Services\Nutrition\FoodAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakeFoodAnalysisProvider;
use Tests\TestCase;

class FoodAnalysisFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_analysis_stays_a_private_draft_until_user_confirms_it(): void
    {
        Storage::fake('local');
        $provider = new FakeFoodAnalysisProvider;
        $this->app->instance(FoodAnalysisProviderInterface::class, $provider);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/food-analysis', [
            'image' => UploadedFile::fake()->image('nasi.jpg', 800, 600),
        ]);

        $analysis = $user->foodAnalyses()->firstOrFail();
        $response->assertRedirect('/food-analysis/'.$analysis->id);
        $this->assertSame(1, $provider->calls);
        $this->assertSame(FoodAnalysisStatus::Review, $analysis->status);
        $this->assertDatabaseCount('meals', 0);
        Storage::disk('local')->assertExists($analysis->image_path);
        $privatePath = $analysis->image_path;
        $this->get('/storage/'.$privatePath)->assertForbidden();

        $imageResponse = $this->actingAs($user)->get('/food-analysis/'.$analysis->id.'/image')->assertOk();
        $this->assertStringContainsString('private', $imageResponse->headers->get('cache-control'));
        $this->assertStringContainsString('no-store', $imageResponse->headers->get('cache-control'));

        $this->actingAs($user)->post('/food-analysis/'.$analysis->id.'/confirm', [
            'meal_date' => today()->toDateString(),
            'meal_type' => 'lunch',
            'foods' => [
                ['food_name' => 'Nasi putih dikoreksi', 'serving_size' => 120, 'serving_unit' => 'g', 'calories' => 156, 'protein_g' => 3, 'carbohydrates_g' => 34, 'fat_g' => 0.4],
            ],
        ])->assertSessionHasNoErrors();

        $meal = $user->meals()->with('items')->firstOrFail();
        $this->assertSame('156.00', $meal->total_calories);
        $this->assertSame('Nasi putih dikoreksi', $meal->items->first()->food_name);
        $this->assertSame(NutritionSource::AiEstimate, $meal->items->first()->source);
        $this->assertSame(FoodAnalysisStatus::Confirmed, $analysis->refresh()->status);
        $this->assertNull($analysis->image_path);
        Storage::disk('local')->assertMissing($privatePath);
    }

    public function test_provider_failure_never_creates_a_meal_and_cleans_up_the_image(): void
    {
        Storage::fake('local');
        $provider = new FakeFoodAnalysisProvider;
        $provider->shouldFail = true;
        $this->app->instance(FoodAnalysisProviderInterface::class, $provider);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/food-analysis', [
            'image' => UploadedFile::fake()->image('timeout.jpg'),
        ])->assertSessionHasErrors('image');

        $analysis = $user->foodAnalyses()->firstOrFail();
        $this->assertSame(FoodAnalysisStatus::Failed, $analysis->status);
        $this->assertNull($analysis->image_path);
        $this->assertDatabaseCount('meals', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_low_confidence_result_is_explicitly_flagged(): void
    {
        Storage::fake('local');
        $provider = new FakeFoodAnalysisProvider;
        $provider->confidence = 0.25;
        $this->app->instance(FoodAnalysisProviderInterface::class, $provider);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/food-analysis', [
            'image' => UploadedFile::fake()->image('uncertain.jpg'),
        ]);

        $analysis = $user->foodAnalyses()->firstOrFail();
        $this->actingAs($user)->get('/food-analysis/'.$analysis->id)
            ->assertOk()->assertSee(__('nutrition.low_confidence'));
    }

    public function test_expired_private_images_are_cleaned_up(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('food-analysis/1/expired.jpg', 'image');
        $user = User::factory()->create();
        $analysis = $user->foodAnalyses()->create([
            'image_path' => 'food-analysis/1/expired.jpg',
            'status' => FoodAnalysisStatus::Review,
            'result' => ['foods' => []],
            'confidence' => 0.2,
            'expires_at' => now()->subMinute(),
        ]);

        $this->assertSame(1, app(FoodAnalysisService::class)->deleteExpiredImages());
        Storage::disk('local')->assertMissing('food-analysis/1/expired.jpg');
        $this->assertNull($analysis->refresh()->image_path);
    }
}
