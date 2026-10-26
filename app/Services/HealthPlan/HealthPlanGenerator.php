<?php

namespace App\Services\HealthPlan;

use App\Enums\HealthGoalStatus;
use App\Enums\HealthGoalType;
use App\Enums\HealthPlanTaskCompletionMode;
use App\Enums\HealthPlanTaskSource;
use App\Enums\HealthPlanTaskType;
use App\Models\User;
use Carbon\CarbonInterface;

class HealthPlanGenerator
{
    /** @return list<array<string, mixed>> */
    public function tasks(User $user, CarbonInterface $date): array
    {
        $tasks = [];
        $activeGoals = $user->healthGoals()->where('status', HealthGoalStatus::Active->value)->get();
        $nutritionProfile = $user->nutritionProfile()->first();
        $hasNutritionGoal = $activeGoals->contains(fn ($goal): bool => in_array($goal->type, [
            HealthGoalType::DailyCalories,
            HealthGoalType::NutritionLogging,
        ], true));

        if ($nutritionProfile || $hasNutritionGoal) {
            foreach (['breakfast' => 10, 'lunch' => 20, 'dinner' => 30] as $meal => $priority) {
                $tasks[] = $this->task(
                    HealthPlanTaskType::NutritionLog,
                    'wellness.plan_task_log_'.$meal,
                    'wellness.plan_task_log_meal_description',
                    $priority,
                    HealthPlanTaskSource::Nutrition,
                    'meal:'.$meal,
                    HealthPlanTaskCompletionMode::Data,
                );
            }
        }

        $hasJournalGoal = $activeGoals->contains('type', HealthGoalType::JournalFrequency);
        if ($user->healthProfile()->exists() || $hasJournalGoal || $user->healthJournals()->exists()) {
            $tasks[] = $this->task(
                HealthPlanTaskType::JournalEntry,
                'wellness.plan_task_journal',
                'wellness.plan_task_journal_description',
                40,
                HealthPlanTaskSource::Journal,
                'journal:'.$date->toDateString(),
                HealthPlanTaskCompletionMode::Data,
            );
        }

        if ($nutritionProfile || $activeGoals->contains('type', HealthGoalType::DailyCalories)) {
            $tasks[] = $this->task(
                HealthPlanTaskType::ReviewNutrition,
                'wellness.plan_task_review_nutrition',
                'wellness.plan_task_review_nutrition_description',
                50,
                HealthPlanTaskSource::Nutrition,
                'review:nutrition',
                HealthPlanTaskCompletionMode::Manual,
            );
        }

        if ($activeGoals->isNotEmpty()) {
            $tasks[] = $this->task(
                HealthPlanTaskType::ReviewHealthGoal,
                'wellness.plan_task_review_goal',
                'wellness.plan_task_review_goal_description',
                60,
                HealthPlanTaskSource::HealthGoal,
                'review:active_goals',
                HealthPlanTaskCompletionMode::Manual,
            );
        }

        if ($user->bmiRecords()->exists()) {
            $tasks[] = $this->task(
                HealthPlanTaskType::ReviewBmi,
                'wellness.plan_task_review_bmi',
                'wellness.plan_task_review_bmi_description',
                65,
                HealthPlanTaskSource::Bmi,
                'review:bmi',
                HealthPlanTaskCompletionMode::Manual,
            );
        }

        $tasks[] = $this->task(
            HealthPlanTaskType::WellnessActivity,
            'wellness.plan_task_wellness',
            'wellness.plan_task_wellness_description',
            70,
            HealthPlanTaskSource::Wellness,
            'wellness:daily',
            HealthPlanTaskCompletionMode::Manual,
        );

        return $tasks;
    }

    /** @return array<string, mixed> */
    private function task(
        HealthPlanTaskType $type,
        string $title,
        string $description,
        int $priority,
        HealthPlanTaskSource $source,
        string $reference,
        HealthPlanTaskCompletionMode $completionMode,
    ): array {
        return compact('type', 'title', 'description', 'priority', 'source') + [
            'source_reference' => $reference,
            'completion_mode' => $completionMode,
        ];
    }
}
