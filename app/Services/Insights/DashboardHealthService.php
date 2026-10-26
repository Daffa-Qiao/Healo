<?php

namespace App\Services\Insights;

use App\Enums\HealthGoalStatus;
use App\Models\User;
use App\Services\HealthPlan\HealthPlanService;
use App\Services\Nutrition\NutritionSummaryService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DashboardHealthService
{
    public function __construct(
        private readonly NutritionSummaryService $nutrition,
        private readonly HealthInsightsService $insights,
        private readonly HealthGoalService $goals,
        private readonly HealthPlanService $plans,
    ) {}

    /** @return array<string, mixed> */
    public function forUser(User $user): array
    {
        $today = CarbonImmutable::today();
        $latestBmi = $user->bmiRecords()->latest('measured_at')->first();
        $weekAgoBmi = $user->bmiRecords()->where('measured_at', '<=', $today->subDays(7)->endOfDay())
            ->latest('measured_at')->first();
        $todayNutrition = $this->nutrition->refresh($user, $today);
        $todayMeals = $user->meals()->whereDate('meal_date', $today)->get();
        $mainMeals = $todayMeals->pluck('meal_type')->map(fn ($type): string => is_string($type) ? $type : $type->value)
            ->intersect(['breakfast', 'lunch', 'dinner'])->unique()->count();
        $journalToday = $user->healthJournals()->whereDate('journal_date', $today)->exists();
        $plan = $this->plans->today($user, false);
        $goalModels = $user->healthGoals()->where('status', HealthGoalStatus::Active->value)
            ->oldest('target_date')->limit(2)->get();
        $goalModels->each(fn ($goal) => $goal->setRelation('user', $user));

        return [
            'profile' => $user->healthProfile()->first(),
            'latest_bmi' => $latestBmi,
            'bmi_week_change' => $latestBmi && $weekAgoBmi
                ? round((float) $latestBmi->bmi - (float) $weekAgoBmi->bmi, 1)
                : null,
            'today_nutrition' => $todayNutrition,
            'calorie_percentage' => $todayNutrition->calorie_target > 0
                ? min(100, (int) round(((float) $todayNutrition->calories_consumed / $todayNutrition->calorie_target) * 100))
                : null,
            'main_meals_logged' => $mainMeals,
            'journal_today' => $journalToday,
            'goals' => $goalModels->map(function ($goal): array {
                return [
                    'model' => $goal,
                    'progress' => $this->goals->progress($goal),
                ];
            }),
            'trends' => collect([7, 30, 90])->mapWithKeys(fn (int $days): array => [
                $days => $this->insights->trends($user, $days)['bmi'],
            ])->all(),
            'weekly' => $this->weeklyComparison($user, $today),
            'insight' => $this->insights->personalized($user, 7),
            'plan' => $plan,
            'plan_metrics' => $plan ? $this->plans->metrics($plan) : null,
        ];
    }

    /** @return array<string, mixed> */
    private function weeklyComparison(User $user, CarbonImmutable $today): array
    {
        $currentStart = $today->subDays(6);
        $previousStart = $currentStart->subDays(7);
        $previousEnd = $currentStart->subDay();
        $currentMeals = $user->meals()->whereBetween('meal_date', [$currentStart, $today])->get();
        $previousMeals = $user->meals()->whereBetween('meal_date', [$previousStart, $previousEnd])->get();
        $currentJournals = $user->healthJournals()->whereBetween('journal_date', [$currentStart, $today])->get();
        $previousJournalDays = $user->healthJournals()->whereBetween('journal_date', [$previousStart, $previousEnd])
            ->distinct()->count('journal_date');
        $currentWeight = $user->bmiRecords()->where('measured_at', '<=', $today->endOfDay())->latest('measured_at')->first();
        $previousWeight = $user->bmiRecords()->where('measured_at', '<=', $previousEnd->endOfDay())->latest('measured_at')->first();
        $currentAverage = $this->averageCalories($currentMeals);
        $previousAverage = $this->averageCalories($previousMeals);
        $mood = $currentJournals->pluck('mood')->filter()->map->value->countBy()->sortDesc()->keys()->first();
        $currentJournalDays = $currentJournals->pluck('journal_date')->map->toDateString()->unique()->count();

        return [
            'start' => $currentStart,
            'end' => $today,
            'average_calories' => $currentAverage,
            'calorie_change_percentage' => $previousAverage > 0
                ? round((($currentAverage - $previousAverage) / $previousAverage) * 100)
                : null,
            'journal_days' => $currentJournalDays,
            'journal_day_change' => $currentJournalDays - $previousJournalDays,
            'weight' => $currentWeight ? (float) $currentWeight->weight_kg : null,
            'weight_change' => $currentWeight && $previousWeight
                ? round((float) $currentWeight->weight_kg - (float) $previousWeight->weight_kg, 1)
                : null,
            'mood' => $mood,
        ];
    }

    /** @param Collection<int, mixed> $meals */
    private function averageCalories(Collection $meals): float
    {
        $days = $meals->groupBy(fn ($meal): string => $meal->meal_date->toDateString());

        return $days->isEmpty() ? 0 : round((float) $days->map->sum('total_calories')->avg(), 1);
    }
}
