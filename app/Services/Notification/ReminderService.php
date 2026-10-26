<?php

namespace App\Services\Notification;

use App\Enums\HealthGoalStatus;
use App\Enums\HealthPlanTaskStatus;
use App\Enums\NotificationType;
use App\Jobs\SendWellnessReminderJob;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Insights\HealthGoalService;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReminderService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly HealthGoalService $goals,
    ) {}

    /** @return Collection<int, UserNotification> */
    public function generateFor(User $user, CarbonInterface $date): Collection
    {
        $created = collect();
        $day = $date->toDateString();

        if (! $user->healthJournals()->whereDate('journal_date', $day)->exists()) {
            $created->push($this->notifications->create(
                $user,
                NotificationType::JournalReminder,
                'Jurnal kesehatan',
                'Luangkan beberapa menit untuk mencatat kondisi dan perasaanmu hari ini.',
                actionUrl: '/health-journals/create',
                referenceDate: $date,
                referenceKey: 'daily-journal',
            ));
        }

        if (! $user->meals()->whereDate('meal_date', $day)->exists()) {
            $created->push($this->notifications->create(
                $user,
                NotificationType::MealReminder,
                'Nutrisi hari ini',
                'Jangan lupa mencatat makananmu agar ringkasan nutrisi tetap lengkap.',
                actionUrl: '/meals/create',
                referenceDate: $date,
                referenceKey: 'daily-meal',
            ));
        }

        $pendingTasks = $user->healthPlans()
            ->whereDate('plan_date', $day)
            ->whereHas('tasks', fn ($query) => $query->where('status', HealthPlanTaskStatus::Pending))
            ->withCount(['tasks as pending_tasks_count' => fn ($query) => $query->where('status', HealthPlanTaskStatus::Pending)])
            ->first();

        if ($pendingTasks && $pendingTasks->pending_tasks_count > 0) {
            $count = $pendingTasks->pending_tasks_count;
            $created->push($this->notifications->create(
                $user,
                NotificationType::HealthPlan,
                'Rencana sehat',
                "Masih ada {$count} aktivitas kesehatan yang belum selesai hari ini.",
                ['pending_count' => $count],
                '/health-plan',
                $date,
                'daily-health-plan',
            ));
        }

        $user->healthGoals()->where('status', HealthGoalStatus::Active)->get()->each(function ($goal) use ($created, $date, $user): void {
            $goal->setRelation('user', $user);
            $progress = $this->goals->progress($goal);
            if ($progress->percentage < 25) {
                return;
            }

            $created->push($this->notifications->create(
                $user,
                NotificationType::HealthGoal,
                'Progress target kesehatan',
                "Kamu sudah mencapai {$progress->percentage}% target kesehatanmu.",
                ['progress_percentage' => $progress->percentage],
                '/health-goals',
                $date,
                'goal-'.$goal->id,
            ));
        });

        if ($date->isMonday()) {
            $created->push($this->notifications->create(
                $user,
                NotificationType::HealthInsight,
                'Wawasan mingguan',
                'Ringkasan kesehatan mingguanmu sudah tersedia.',
                actionUrl: '/health-insights',
                referenceDate: $date,
                referenceKey: 'weekly-insight',
            ));
        }

        $created->push($this->notifications->create(
            $user,
            NotificationType::Wellness,
            'Pengingat wellness',
            'Satu langkah kecil yang konsisten dapat mendukung kebiasaan sehatmu.',
            actionUrl: '/health-plan',
            referenceDate: $date,
            referenceKey: 'daily-wellness',
        ));

        return $created->filter()->values();
    }

    public function queueForAll(CarbonInterface $date): int
    {
        $count = 0;
        User::query()->where('role', 'user')->select('id')->chunkById(200, function ($users) use (&$count, $date): void {
            foreach ($users as $user) {
                SendWellnessReminderJob::dispatch($user->id, $date->toDateString());
                $count++;
            }
        });

        return $count;
    }

    public function queueDue(CarbonInterface $now): int
    {
        $count = 0;
        User::query()->where('role', 'user')->with('notificationPreference')->select('id', 'role')->chunkById(200, function ($users) use (&$count, $now): void {
            foreach ($users as $user) {
                $time = $user->notificationPreference?->reminder_time ?? '08:00';
                $scheduled = Carbon::parse($now->toDateString().' '.$time, config('app.timezone'));
                if ($now->greaterThanOrEqualTo($scheduled) && $now->diffInMinutes($scheduled) < 15) {
                    SendWellnessReminderJob::dispatch($user->id, $now->toDateString());
                    $count++;
                }
            }
        });

        return $count;
    }
}
