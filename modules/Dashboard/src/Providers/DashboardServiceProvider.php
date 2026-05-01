<?php

declare(strict_types=1);

namespace Modules\Dashboard\src\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Dashboard\src\Application\Services\DashboardService;
use Modules\Dashboard\src\Domain\Repositories\DashboardRepositoryInterface;
use Modules\Dashboard\src\Infrastructure\Repositories\DashboardRepository;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Repository
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );
        // Service
        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService($app->make(DashboardRepository::class));
        });
    }
}
