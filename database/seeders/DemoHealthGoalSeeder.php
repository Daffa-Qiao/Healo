<?php

namespace Database\Seeders;

use App\Enums\HealthGoalStatus;
use App\Enums\HealthGoalType;
use App\Models\User;
use App\Services\Insights\HealthGoalService;
use Illuminate\Database\Seeder;

class DemoHealthGoalSeeder extends Seeder
{
    public function run(HealthGoalService $service): void
    {
        $user = User::query()->where('email', 'demo@healo.test')->firstOrFail();
        $goals = [
            [
                'type' => HealthGoalType::JournalFrequency,
                'target_value' => 4,
                'start_date' => '2026-09-22',
                'target_date' => '2026-12-31',
                'status' => HealthGoalStatus::Active,
            ],
            [
                'type' => HealthGoalType::NutritionLogging,
                'target_value' => 14,
                'start_date' => '2026-09-22',
                'target_date' => '2026-12-31',
                'status' => HealthGoalStatus::Active,
            ],
        ];

        foreach ($goals as $data) {
            $exists = $user->healthGoals()
                ->where('type', $data['type']->value)
                ->whereDate('start_date', $data['start_date'])
                ->exists();

            if (! $exists) {
                $service->create($user, [
                    ...$data,
                    'type' => $data['type']->value,
                    'status' => $data['status']->value,
                ]);
            }
        }
    }
}
