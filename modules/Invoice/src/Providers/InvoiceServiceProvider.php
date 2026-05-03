<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Invoice\src\Application\Services\InvoiceService;
use Modules\Invoice\src\Application\Services\Monitoring\InvoiceErrorReporter;
use Modules\Invoice\src\Domain\Entities\Invoice;
use Modules\Invoice\src\Domain\Observers\InvoiceObserver;
use Modules\Invoice\src\Domain\Repositories\InvoiceItemRepositoryInterface;
use Modules\Invoice\src\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoice\src\Infrastructure\Repositories\InvoiceItemRepository;
use Modules\Invoice\src\Infrastructure\Repositories\InvoiceRepository;
use Modules\Service\src\Domain\Repositories\ServiceRepositoryInterface;

class InvoiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(
            InvoiceRepositoryInterface::class,
            InvoiceRepository::class
        );

        $this->app->bind(
            InvoiceItemRepositoryInterface::class,
            InvoiceItemRepository::class
        );

        // Services
        $this->app->singleton(InvoiceService::class, function ($app) {
            return new InvoiceService(
                $app->make(InvoiceRepositoryInterface::class),
                $app->make(InvoiceItemRepositoryInterface::class),
                $app->make(ServiceRepositoryInterface::class),
                $app->make(InvoiceErrorReporter::class)
            );
        });

        //Observer
        $this->app->booted(function () {
            Invoice::observe(InvoiceObserver::class);
        });
    }
}
