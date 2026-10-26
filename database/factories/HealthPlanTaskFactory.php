<?php

namespace Database\Factories;

use App\Enums\HealthPlanTaskCompletionMode;
use App\Enums\HealthPlanTaskSource;
use App\Enums\HealthPlanTaskStatus;
use App\Enums\HealthPlanTaskType;
use App\Models\HealthPlan;
use App\Models\HealthPlanTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HealthPlanTask> */
class HealthPlanTaskFactory extends Factory
{
    protected $model = HealthPlanTask::class;

    public function definition(): array
    {
        return [
            'health_plan_id' => HealthPlan::factory(),
            'type' => HealthPlanTaskType::WellnessActivity,
            'title' => 'wellness.plan_task_wellness',
            'description' => 'wellness.plan_task_wellness_description',
            'status' => HealthPlanTaskStatus::Pending,
            'priority' => 70,
            'source' => HealthPlanTaskSource::Wellness,
            'source_reference' => 'daily_wellness',
            'completion_mode' => HealthPlanTaskCompletionMode::Manual,
        ];
    }
}
