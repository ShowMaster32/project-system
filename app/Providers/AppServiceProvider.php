<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // Super admin bypass - ha tutti i permessi automaticamente
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
