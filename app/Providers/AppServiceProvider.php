<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\View;
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
        // All modules registration
        $modulesPath = base_path('modules');

        foreach (glob($modulesPath . '/*/src/Interfaces/Http/views', GLOB_ONLYDIR) as $viewsPath) {
            $moduleName = basename(dirname($viewsPath, 4));
            View::addNamespace(strtolower($moduleName), $viewsPath);
        }
    }
}
