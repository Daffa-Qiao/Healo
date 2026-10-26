<?php

namespace App\Services\Nutrition;

use App\Enums\NutritionSource;
use App\Models\Meal;
use App\Models\MealItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MealItemService
{
    public function __construct(private readonly MealService $meals) {}

    /** @param array<string, mixed> $data */
    public function create(Meal $meal, array $data): MealItem
    {
        return DB::transaction(function () use ($meal, $data): MealItem {
            $item = $meal->items()->create([
                ...Arr::only($data, ['food_name', 'serving_size', 'serving_unit', 'calories', 'protein_g', 'carbohydrates_g', 'fat_g']),
                'source' => NutritionSource::Manual,
            ]);
            $this->meals->recalculate($meal);

            return $item;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(MealItem $item, array $data): MealItem
    {
        return DB::transaction(function () use ($item, $data): MealItem {
            $item->update(Arr::only($data, ['food_name', 'serving_size', 'serving_unit', 'calories', 'protein_g', 'carbohydrates_g', 'fat_g']));
            $this->meals->recalculate($item->meal);

            return $item->refresh();
        });
    }

    public function delete(MealItem $item): void
    {
        DB::transaction(function () use ($item): void {
            $meal = $item->meal;
            $item->delete();
            $this->meals->recalculate($meal);
        });
    }
}
