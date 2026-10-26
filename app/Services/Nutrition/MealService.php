<?php

namespace App\Services\Nutrition;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MealService
{
    public function __construct(private readonly NutritionSummaryService $summaries) {}

    /** @param array<string, mixed> $data */
    public function create(User $user, array $data): Meal
    {
        $meal = $user->meals()->create(Arr::only($data, ['meal_date', 'meal_type', 'notes']));
        $this->summaries->refresh($user, $meal->meal_date);

        return $meal;
    }

    /** @param array<string, mixed> $mealData @param list<array<string, mixed>> $items */
    public function createWithItems(User $user, array $mealData, array $items): Meal
    {
        return DB::transaction(function () use ($user, $mealData, $items): Meal {
            $meal = $user->meals()->create(Arr::only($mealData, ['meal_date', 'meal_type', 'notes']));
            $meal->items()->createMany($items);
            $this->recalculate($meal);

            return $meal->load('items');
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Meal $meal, array $data): Meal
    {
        $oldDate = $meal->meal_date->toDateString();
        $meal->update(Arr::only($data, ['meal_date', 'meal_type', 'notes']));
        $this->summaries->refresh($meal->user, $oldDate);
        $this->summaries->refresh($meal->user, $meal->meal_date);

        return $meal->refresh()->load('items');
    }

    public function delete(Meal $meal): void
    {
        $user = $meal->user;
        $date = $meal->meal_date;
        $meal->delete();
        $this->summaries->refresh($user, $date);
    }

    public function recalculate(Meal $meal): Meal
    {
        $meal->forceFill(['total_calories' => $meal->items()->sum('calories')])->save();
        $this->summaries->refresh($meal->user, $meal->meal_date);

        return $meal->refresh();
    }
}
