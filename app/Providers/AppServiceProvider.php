<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
