<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Notification\ReminderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class SendWellnessReminderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public readonly int $userId,
        public readonly string $referenceDate,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(ReminderService $reminders): void
    {
        $user = User::query()->whereKey($this->userId)->where('role', 'user')->first();
        if (! $user) {
            return;
        }

        $reminders->generateFor($user, Carbon::parse($this->referenceDate, config('app.timezone')));
    }
}
