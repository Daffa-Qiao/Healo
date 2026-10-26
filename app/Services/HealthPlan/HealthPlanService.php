<?php

namespace App\Services\HealthPlan;

use App\Enums\HealthPlanStatus;
use App\Enums\HealthPlanTaskCompletionMode;
use App\Enums\HealthPlanTaskStatus;
use App\Models\HealthPlan;
use App\Models\HealthPlanTask;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HealthPlanService
{
    public function __construct(
        private readonly HealthPlanGenerator $generator,
        private readonly HealthPlanProgressService $progress,
    ) {}

    public function generate(User $user, CarbonInterface|string|null $date = null): HealthPlan
    {
        $date = CarbonImmutable::parse($date ?? today())->startOfDay();

        return DB::transaction(function () use ($user, $date): HealthPlan {
            $plan = $user->healthPlans()->whereDate('plan_date', $date->toDateString())->first()
                ?? $user->healthPlans()->create([
                    'plan_date' => $date->toDateString(),
                    'status' => HealthPlanStatus::Active,
                    'progress_percentage' => 0,
                    'generated_at' => now(),
                ]);

            foreach ($this->generator->tasks($user, $date) as $definition) {
                $identity = [
                    'type' => $definition['type']->value,
                    'source_reference' => $definition['source_reference'],
                ];
                $task = $plan->tasks()->firstOrNew($identity);
                $task->fill([
                    ...$definition,
                    'type' => $definition['type']->value,
                    'source' => $definition['source']->value,
                    'completion_mode' => $definition['completion_mode']->value,
                ])->save();
            }

            $plan->setRelation('user', $user);

            return $this->progress->synchronize($plan);
        });
    }

    public function today(User $user, bool $generate = true): ?HealthPlan
    {
        $plan = $user->healthPlans()->whereDate('plan_date', today())->first();
        if (! $plan && $generate) {
            return $this->generate($user);
        }

        if (! $plan) {
            return null;
        }

        $plan->setRelation('user', $user);

        return $this->progress->synchronize($plan);
    }

    public function complete(HealthPlanTask $task): HealthPlan
    {
        $this->ensureManual($task);
        $task->forceFill(['status' => HealthPlanTaskStatus::Completed, 'completed_at' => now()])->save();

        return $this->progress->recalculate($task->healthPlan);
    }

    public function uncomplete(HealthPlanTask $task): HealthPlan
    {
        $this->ensureManual($task);
        $task->forceFill(['status' => HealthPlanTaskStatus::Pending, 'completed_at' => null])->save();

        return $this->progress->recalculate($task->healthPlan);
    }

    /** @return array{total: int, completed: int, remaining: int, percentage: int} */
    public function metrics(HealthPlan $plan): array
    {
        $plan->loadMissing('tasks');
        $total = $plan->tasks->count();
        $completed = $plan->tasks->where('status', HealthPlanTaskStatus::Completed)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'remaining' => max(0, $total - $completed),
            'percentage' => $plan->progress_percentage,
        ];
    }

    private function ensureManual(HealthPlanTask $task): void
    {
        if ($task->completion_mode !== HealthPlanTaskCompletionMode::Manual) {
            throw ValidationException::withMessages([
                'task' => __('wellness.data_task_requires_source_action'),
            ]);
        }
    }
}
