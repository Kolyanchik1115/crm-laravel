<?php

declare(strict_types=1);

namespace Modules\Dashboard\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Dashboard\Application\Services\DashboardService;
use App\Events\TransferCompleted;
use App\Events\InvoiceCreated;
use Modules\Dashboard\Infrastructure\Repositories\DashboardRepository;
use Modules\Dashboard\Application\Listeners\UpdateDashboardCacheListener;
use Modules\Dashboard\Application\Listeners\UpdateDashboardCacheOnInvoiceListener;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Services
        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService($app->make(DashboardRepository::class));
        });
    }

    public function boot(): void
    {
        //routes
        $this->loadRoutesFrom(__DIR__ . '/../Interfaces/Http/routes/web.php');

        //views
        $this->loadViewsFrom(__DIR__ . '/../Interfaces/views', 'dashboard');

        // TODO: should i move this to bootstrap.app config file??
        //  listeners
        Event::listen(
            TransferCompleted::class,
            UpdateDashboardCacheListener::class
        );

        Event::listen(
            InvoiceCreated::class,
            UpdateDashboardCacheOnInvoiceListener::class
        );
    }
}
