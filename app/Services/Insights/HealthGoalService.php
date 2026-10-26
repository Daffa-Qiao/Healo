<?php

namespace App\Services\Insights;

use App\Data\HealthGoalProgress;
use App\Enums\HealthGoalStatus;
use App\Enums\HealthGoalType;
use App\Models\HealthGoal;
use App\Models\User;
use Illuminate\Support\Arr;

class HealthGoalService
{
    /** @param array<string, mixed> $data */
    public function create(User $user, array $data): HealthGoal
    {
        $type = HealthGoalType::from($data['type']);
        $startDate = $data['start_date'] ?? today()->toDateString();
        $baseline = $this->currentValueFor($user, $type, $startDate);

        return $user->healthGoals()->create([
            ...Arr::only($data, ['type', 'target_value', 'start_date', 'target_date', 'status']),
            'start_date' => $startDate,
            'status' => $data['status'] ?? HealthGoalStatus::Active,
            'unit' => $this->unitFor($type),
            'current_value' => null,
            'metadata' => ['starting_value' => $baseline],
        ]);
    }

    /** @param array<string, mixed> $data */
    public function update(HealthGoal $goal, array $data): HealthGoal
    {
        $updates = Arr::only($data, ['target_value', 'start_date', 'target_date', 'status']);
        $newStartDate = (string) ($data['start_date'] ?? $goal->start_date->toDateString());
        if ($newStartDate !== $goal->start_date->toDateString()) {
            $updates['metadata'] = [
                ...(is_array($goal->metadata) ? $goal->metadata : []),
                'starting_value' => $this->currentValueFor($goal->user, $goal->type, $newStartDate),
            ];
        }
        $goal->update($updates);

        return $goal->refresh();
    }

    public function delete(HealthGoal $goal): void
    {
        $goal->delete();
    }

    public function progress(HealthGoal $goal): HealthGoalProgress
    {
        $current = $this->currentValueFor($goal->user, $goal->type, $goal->start_date->toDateString());
        $target = (float) $goal->target_value;
        $baseline = is_numeric(data_get($goal->metadata, 'starting_value'))
            ? (float) data_get($goal->metadata, 'starting_value')
            : $current;
        $percentage = $this->percentage($goal->type, $baseline, $current, $target);
        $status = $goal->status;

        if ($status === HealthGoalStatus::Active && $percentage >= 100) {
            $status = HealthGoalStatus::Completed;
        }

        return new HealthGoalProgress(
            currentValue: $current,
            targetValue: $target,
            remainingValue: $current === null ? null : round(abs($target - $current), 2),
            percentage: $percentage,
            status: $status,
            unit: $goal->unit,
        );
    }

    public function currentValueFor(User $user, HealthGoalType $type, string $startDate): ?float
    {
        return match ($type) {
            HealthGoalType::Weight => $this->latestWeight($user),
            HealthGoalType::Bmi => $this->latestBmi($user),
            HealthGoalType::DailyCalories => $this->averageCalories($user, $startDate),
            HealthGoalType::JournalFrequency => (float) $user->healthJournals()
                ->whereDate('journal_date', '>=', max($startDate, today()->subDays(6)->toDateString()))->count(),
            HealthGoalType::NutritionLogging => (float) $user->meals()
                ->whereDate('meal_date', '>=', max($startDate, today()->subDays(6)->toDateString()))->count(),
        };
    }

    public function unitFor(HealthGoalType $type): string
    {
        return match ($type) {
            HealthGoalType::Weight => 'kg',
            HealthGoalType::Bmi => 'BMI',
            HealthGoalType::DailyCalories => 'kcal/day',
            HealthGoalType::JournalFrequency => 'entries/week',
            HealthGoalType::NutritionLogging => 'meals/week',
        };
    }

    private function percentage(HealthGoalType $type, ?float $baseline, ?float $current, float $target): float
    {
        if ($current === null || $target <= 0) {
            return 0;
        }

        if (in_array($type, [HealthGoalType::Weight, HealthGoalType::Bmi], true)) {
            if ($baseline === null || abs($baseline - $target) < 0.001) {
                return abs($current - $target) < 0.01 ? 100 : 0;
            }
            $value = (($current - $baseline) / ($target - $baseline)) * 100;
        } elseif ($type === HealthGoalType::DailyCalories) {
            $value = 100 - (abs($current - $target) / $target * 100);
        } else {
            $value = $current / $target * 100;
        }

        return round(max(0, min(100, $value)), 1);
    }

    private function latestWeight(User $user): ?float
    {
        $value = $user->bmiRecords()->latest('measured_at')->value('weight_kg')
            ?? $user->healthProfile()->value('weight_kg');

        return $value !== null ? (float) $value : null;
    }

    private function latestBmi(User $user): ?float
    {
        $value = $user->bmiRecords()->latest('measured_at')->value('bmi');

        return $value !== null ? (float) $value : null;
    }

    private function averageCalories(User $user, string $startDate): ?float
    {
        $value = $user->nutritionDailySummaries()
            ->whereDate('summary_date', '>=', max($startDate, today()->subDays(6)->toDateString()))
            ->where('meals_logged', '>', 0)->avg('calories_consumed');

        return $value !== null ? round((float) $value, 2) : null;
    }
}
