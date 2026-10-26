<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Development seed data was skipped outside local/testing.');

            return;
        }

        $this->call([
            DevelopmentAccountSeeder::class,
            DemoHealthDataSeeder::class,
            HealthyMenuSeeder::class,
            DemoNutritionDataSeeder::class,
            DemoHealthAIDataSeeder::class,
            DemoHealthGoalSeeder::class,
            DemoHealthPlanSeeder::class,
            DemoNotificationSeeder::class,
        ]);
    }
}
