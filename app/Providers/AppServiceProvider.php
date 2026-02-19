<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\TaskObserver;
use Illuminate\Support\Facades\Gate;
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
        // ─── Observers ────────────────────────────────────────────────────────
        Task::observe(TaskObserver::class);

        // ─── Super admin bypass ───────────────────────────────────────────────
        // Ha tutti i permessi automaticamente
        Gate::before(function ($user, $ability) {
            // Super admin Spatie
            if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                return true;
            }

            // Global admin da config
            if (method_exists($user, 'isGlobalAdmin') && $user->isGlobalAdmin()) {
                return true;
            }
        });
    }
}
