<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

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
    // app/Providers/AppServiceProvider.php
    public function boot(): void
    {
        RateLimiter::for('api', function ($job) {
            return Limit::perMinute(60)->by($job->user()?->id ?: $job->ip());
        });

        $modulesPath = base_path('modules');

        // Migrations
        foreach (glob(
            $modulesPath . '/*/src/Infrastructure/Database/Migrations',
            GLOB_ONLYDIR
        ) as $migrationsPath) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        // Views
        foreach (glob($modulesPath . '/*/src/Interfaces/Http/views', GLOB_ONLYDIR) as $viewsPath) {
            $moduleName = basename(dirname($viewsPath, 4));
            View::addNamespace(strtolower($moduleName), $viewsPath);
        }
    }
}
