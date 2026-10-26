<?php

namespace App\Console\Commands;

use App\Services\Notification\NotificationService;
use Illuminate\Console\Command;

class DeliverNotificationsCommand extends Command
{
    protected $signature = 'healo:deliver-notifications';

    protected $description = 'Broadcast stored notifications whose quiet-hours delay has ended';

    public function handle(NotificationService $notifications): int
    {
        $count = $notifications->deliverDue();
        $this->info("Delivered {$count} deferred notification(s).");

        return self::SUCCESS;
    }
}
