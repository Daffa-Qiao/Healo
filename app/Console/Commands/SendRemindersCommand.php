<?php

namespace App\Console\Commands;

use App\Services\Notification\ReminderService;
use Illuminate\Console\Command;

class SendRemindersCommand extends Command
{
    protected $signature = 'healo:send-reminders {--date= : Reference date in YYYY-MM-DD format} {--respect-preferences : Queue only users whose reminder time is due}';

    protected $description = 'Queue owner-scoped HEALO wellness reminders';

    public function handle(ReminderService $reminders): int
    {
        $date = $this->option('date') ? now()->parse($this->option('date')) : today();
        $count = $this->option('respect-preferences')
            ? $reminders->queueDue(now())
            : $reminders->queueForAll($date);
        $this->info("Queued reminders for {$count} user(s).");

        return self::SUCCESS;
    }
}
