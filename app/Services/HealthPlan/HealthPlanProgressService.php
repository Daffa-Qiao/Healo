<?php

namespace App\Services\HealthPlan;

use App\Enums\HealthPlanStatus;
use App\Enums\HealthPlanTaskCompletionMode;
use App\Enums\HealthPlanTaskStatus;
use App\Enums\HealthPlanTaskType;
use App\Models\HealthPlan;
use App\Models\HealthPlanTask;

class HealthPlanProgressService
{
    public function synchronize(HealthPlan $plan): HealthPlan
    {
        $plan->loadMissing(['user', 'tasks']);
        $date = $plan->plan_date->toDateString();
        $mealTypes = $plan->user->meals()->whereDate('meal_date', $date)->pluck('meal_type')
            ->map(fn ($type): string => is_string($type) ? $type : $type->value)->all();
        $hasJournal = $plan->user->healthJournals()->whereDate('journal_date', $date)->exists();

        foreach ($plan->tasks as $task) {
            if ($task->completion_mode !== HealthPlanTaskCompletionMode::Data) {
                continue;
            }

            $complete = match ($task->type) {
                HealthPlanTaskType::NutritionLog => in_array(str($task->source_reference)->after('meal:')->toString(), $mealTypes, true),
                HealthPlanTaskType::JournalEntry => $hasJournal,
                default => false,
            };
            $this->setStatus($task, $complete);
        }

        return $this->recalculate($plan);
    }

    public function recalculate(HealthPlan $plan): HealthPlan
    {
        $plan->load('tasks');
        $total = $plan->tasks->count();
        $completed = $plan->tasks->where('status', HealthPlanTaskStatus::Completed)->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
        $isComplete = $total > 0 && $completed === $total;

        $plan->forceFill([
            'progress_percentage' => min(100, max(0, $percentage)),
            'status' => $isComplete ? HealthPlanStatus::Completed : HealthPlanStatus::Active,
            'completed_at' => $isComplete ? ($plan->completed_at ?? now()) : null,
        ])->save();

        return $plan->refresh()->load('tasks');
    }

    private function setStatus(HealthPlanTask $task, bool $complete): void
    {
        $status = $complete ? HealthPlanTaskStatus::Completed : HealthPlanTaskStatus::Pending;
        if ($task->status !== $status) {
            $task->forceFill([
                'status' => $status,
                'completed_at' => $complete ? now() : null,
            ])->save();
        }
    }
}
