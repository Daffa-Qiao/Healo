<?php

namespace App\Services\Nutrition;

use App\Models\NutritionDailySummary;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class NutritionSummaryService
{
    public function refresh(User $user, CarbonInterface|string $date): NutritionDailySummary
    {
        $date = CarbonImmutable::parse($date)->startOfDay();
        $aggregate = $user->meals()
            ->whereDate('meal_date', $date)
            ->selectRaw('COALESCE(SUM(total_calories), 0) as calories, COUNT(*) as meals_count')
            ->first();
        $target = (int) ($user->nutritionProfile()->value('daily_calorie_target') ?? 0);
        $consumed = round((float) $aggregate->calories, 2);

        $summary = $user->nutritionDailySummaries()
            ->whereDate('summary_date', $date->toDateString())
            ->first() ?? $user->nutritionDailySummaries()->make(['summary_date' => $date->toDateString()]);
        $summary->fill([
            'calorie_target' => $target,
            'calories_consumed' => $consumed,
            'calories_remaining' => round($target - $consumed, 2),
            'meals_logged' => (int) $aggregate->meals_count,
        ])->save();

        return $summary;
    }

    /** @return Collection<int, array{date: CarbonImmutable, target: int, consumed: float, remaining: float, meals: int}> */
    public function weekly(User $user, ?CarbonInterface $ending = null): Collection
    {
        $end = CarbonImmutable::instance($ending ?? now())->startOfDay();
        $start = $end->subDays(6);
        $target = (int) ($user->nutritionProfile()->value('daily_calorie_target') ?? 0);
        $meals = $user->meals()
            ->whereBetween('meal_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('meal_date, COALESCE(SUM(total_calories), 0) as calories, COUNT(*) as meals_count')
            ->groupBy('meal_date')
            ->get()
            ->keyBy(fn ($row): string => CarbonImmutable::parse($row->meal_date)->toDateString());

        return collect(range(0, 6))->map(function (int $offset) use ($start, $meals, $target): array {
            $date = $start->addDays($offset);
            $row = $meals->get($date->toDateString());
            $consumed = round((float) ($row->calories ?? 0), 2);

            return [
                'date' => $date,
                'target' => $target,
                'consumed' => $consumed,
                'remaining' => round($target - $consumed, 2),
                'meals' => (int) ($row->meals_count ?? 0),
            ];
        });
    }

    /** @param Collection<int, array{date: CarbonImmutable, target: int, consumed: float, remaining: float, meals: int}> $days */
    public function weeklyStats(Collection $days): array
    {
        $logged = $days->where('meals', '>', 0);

        return [
            'logged_days' => $logged->count(),
            'average' => $logged->isEmpty() ? 0 : round((float) $logged->avg('consumed'), 1),
            'highest' => $logged->sortByDesc('consumed')->first(),
            'lowest' => $logged->sortBy('consumed')->first(),
            'meals' => (int) $days->sum('meals'),
        ];
    }
}
