<?php

declare(strict_types=1);

namespace Modules\Dashboard\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Dashboard\Application\Services\DashboardService;
use Modules\Dashboard\Infrastructure\Repositories\DashboardRepository;
use Modules\Transaction\Domain\Events\TransferCompleted;
use Modules\Invoice\Domain\Events\InvoiceCreated;
use Modules\Dashboard\Application\Listeners\UpdateDashboardCacheListener;
use Modules\Dashboard\Application\Listeners\UpdateDashboardCacheOnInvoiceListener;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Service
        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService($app->make(DashboardRepository::class));
        });
    }
}
