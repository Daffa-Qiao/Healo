<?php

namespace App\Services\Nutrition;

use App\Contracts\FoodAnalysisProviderInterface;
use App\Enums\FoodAnalysisStatus;
use App\Enums\NutritionSource;
use App\Exceptions\FoodAnalysisException;
use App\Models\FoodAnalysis;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FoodAnalysisService
{
    public function __construct(
        private readonly FoodAnalysisProviderInterface $provider,
        private readonly MealService $meals,
    ) {}

    public function analyze(User $user, UploadedFile $image): FoodAnalysis
    {
        $disk = (string) config('nutrition.image_disk', 'local');
        $directory = 'food-analysis/'.$user->getKey();
        $path = $image->store($directory, $disk);

        if (! $path) {
            throw new FoodAnalysisException('food_analysis_upload_failed');
        }

        $analysis = $user->foodAnalyses()->create([
            'image_path' => $path,
            'status' => FoodAnalysisStatus::Processing,
            'expires_at' => now()->addDays((int) config('nutrition.image_retention_days', 30)),
        ]);

        try {
            $result = $this->provider->analyze(
                Storage::disk($disk)->path($path),
                $image->getClientOriginalName(),
            );
            $analysis->update([
                'status' => FoodAnalysisStatus::Review,
                'result' => $result->toArray(),
                'confidence' => $result->confidence,
                'provider' => $result->provider,
                'analyzed_at' => now(),
            ]);

            return $analysis->refresh();
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);
            $analysis->update([
                'image_path' => null,
                'status' => FoodAnalysisStatus::Failed,
                'failure_code' => 'provider_error',
                'expires_at' => null,
            ]);

            Log::warning('Food analysis provider failed.', [
                'provider' => class_basename($this->provider),
                'exception_type' => $exception::class,
            ]);

            throw new FoodAnalysisException('food_analysis_provider_failed', previous: $exception);
        }
    }

    /** @param array<string, mixed> $data */
    public function confirm(FoodAnalysis $analysis, array $data): Meal
    {
        if ($analysis->status !== FoodAnalysisStatus::Review || $analysis->confirmed_at) {
            throw new FoodAnalysisException('food_analysis_not_reviewable');
        }

        return DB::transaction(function () use ($analysis, $data): Meal {
            $confidence = (float) $analysis->confidence;
            $items = collect($data['foods'])->map(fn (array $food): array => [
                'food_name' => $food['food_name'],
                'serving_size' => $food['serving_size'],
                'serving_unit' => $food['serving_unit'],
                'calories' => $food['calories'],
                'protein_g' => $food['protein_g'] ?? 0,
                'carbohydrates_g' => $food['carbohydrates_g'] ?? 0,
                'fat_g' => $food['fat_g'] ?? 0,
                'source' => NutritionSource::AiEstimate,
                'confidence' => $confidence,
            ])->all();

            $meal = $this->meals->createWithItems(
                $analysis->user,
                Arr::only($data, ['meal_date', 'meal_type', 'notes']),
                $items,
            );

            $this->deleteImage($analysis);
            $analysis->update([
                'status' => FoodAnalysisStatus::Confirmed,
                'confirmed_at' => now(),
                'expires_at' => null,
                'image_path' => null,
            ]);

            return $meal;
        });
    }

    public function cancel(FoodAnalysis $analysis): void
    {
        $this->deleteImage($analysis);
        $analysis->delete();
    }

    public function deleteExpiredImages(): int
    {
        $count = 0;
        FoodAnalysis::query()
            ->whereNotNull('image_path')
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($analyses) use (&$count): void {
                foreach ($analyses as $analysis) {
                    $this->deleteImage($analysis);
                    $analysis->update(['image_path' => null, 'expires_at' => null]);
                    $count++;
                }
            });

        return $count;
    }

    private function deleteImage(FoodAnalysis $analysis): void
    {
        if ($analysis->image_path) {
            Storage::disk((string) config('nutrition.image_disk', 'local'))->delete($analysis->image_path);
        }
    }
}
