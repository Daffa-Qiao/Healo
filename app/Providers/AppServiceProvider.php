<?php

namespace App\Providers;

use App\Contracts\FoodAnalysisProviderInterface;
use App\Contracts\HealthAIProviderInterface;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\HealthAI\GroqHealthAIProvider;
use App\Services\HealthAI\LocalDemoHealthAIProvider;
use App\Services\Notification\NotificationService;
use App\Services\Nutrition\GroqFoodAnalysisProvider;
use App\Services\Nutrition\LocalDemoFoodAnalysisProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FoodAnalysisProviderInterface::class, function ($app) {
            return match (config('nutrition.food_analysis_provider')) {
                'groq' => filled(config('services.groq.api_key'))
                    ? $app->make(GroqFoodAnalysisProvider::class)
                    : $app->make(LocalDemoFoodAnalysisProvider::class),
                'local_demo', null => $app->make(LocalDemoFoodAnalysisProvider::class),
                default => $app->make(LocalDemoFoodAnalysisProvider::class),
            };
        });

        $this->app->bind(HealthAIProviderInterface::class, function ($app) {
            return match (config('health_ai.provider')) {
                'groq' => filled(config('services.groq.api_key'))
                    ? $app->make(GroqHealthAIProvider::class)
                    : $app->make(LocalDemoHealthAIProvider::class),
                'local_demo', null => $app->make(LocalDemoHealthAIProvider::class),
                default => $app->make(LocalDemoHealthAIProvider::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('food-analysis', fn (Request $request): Limit => Limit::perMinute(5)
            ->by('food-analysis:'.($request->user()?->getAuthIdentifier() ?? $request->ip())));

        RateLimiter::for('health-ai', fn (Request $request): Limit => Limit::perMinute(
            (int) config('health_ai.rate_limit_per_minute', 10)
        )->by('health-ai:'.($request->user()?->getAuthIdentifier() ?? $request->ip())));

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            if (! $user instanceof User) {
                return;
            }

            $notifications = app(NotificationService::class);
            $view->with([
                'headerNotifications' => $notifications->latest($user),
                'headerUnreadCount' => $notifications->unreadCount($user),
            ]);
        });

        Event::listen(Login::class, function (Login $event): void {
            app(AuditLogService::class)->record($event->user, 'auth.login', $event->user);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user instanceof User) {
                app(AuditLogService::class)->record($event->user, 'auth.logout', $event->user);
            }
        });

        Event::listen(Registered::class, function (Registered $event): void {
            if ($event->user instanceof User) {
                app(AuditLogService::class)->record($event->user, 'auth.registered', $event->user);
            }
        });
    }
}
