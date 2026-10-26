<?php

namespace Database\Seeders;

use App\Enums\NotificationType;
use App\Models\User;
use App\Services\Notification\NotificationPreferenceService;
use App\Services\Notification\NotificationService;
use Illuminate\Database\Seeder;

class DemoNotificationSeeder extends Seeder
{
    public function run(NotificationService $notifications, NotificationPreferenceService $preferences): void
    {
        $user = User::query()->where('email', 'demo@healo.test')->firstOrFail();
        $preferences->update($user, [
            'notifications_enabled' => true,
            'health_plan_enabled' => true,
            'meal_reminder_enabled' => true,
            'journal_reminder_enabled' => true,
            'health_goal_enabled' => true,
            'health_insight_enabled' => true,
            'wellness_enabled' => true,
            'system_enabled' => true,
        ]);

        $items = [
            [NotificationType::HealthPlan, 'Rencana sehat', 'Masih ada aktivitas kesehatan yang bisa kamu selesaikan hari ini.', '/health-plan', 'demo-plan', false],
            [NotificationType::MealReminder, 'Nutrisi hari ini', 'Jangan lupa mencatat makananmu agar ringkasan nutrisi tetap lengkap.', '/meals/create', 'demo-meal', false],
            [NotificationType::JournalReminder, 'Jurnal kesehatan', 'Luangkan beberapa menit untuk mencatat kondisi dan perasaanmu hari ini.', '/health-journals/create', 'demo-journal', false],
            [NotificationType::HealthInsight, 'Wawasan mingguan', 'Ringkasan kesehatan mingguanmu sudah tersedia.', '/health-insights', 'demo-insight', true],
            [NotificationType::System, 'Selamat datang di HEALO', 'Preferensi notifikasi dapat kamu sesuaikan kapan saja.', '/settings/notifications', 'demo-system', true],
        ];

        foreach ($items as [$type, $title, $body, $url, $key, $read]) {
            $notification = $notifications->create($user, $type, $title, $body, actionUrl: $url, referenceKey: $key);
            if ($read && $notification?->read_at === null) {
                $notification->forceFill(['read_at' => now()->subDay()])->save();
            }
        }
    }
}
