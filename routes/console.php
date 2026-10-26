<?php

use App\Services\Nutrition\FoodAnalysisService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('nutrition:cleanup-food-images', function () {
    $count = app(FoodAnalysisService::class)->deleteExpiredImages();
    $this->info("Deleted {$count} expired private food image(s).");
})->purpose('Delete expired private food-analysis images');

Schedule::command('nutrition:cleanup-food-images')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('healo:send-reminders --respect-preferences')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('healo:deliver-notifications')->everyMinute()->withoutOverlapping();
