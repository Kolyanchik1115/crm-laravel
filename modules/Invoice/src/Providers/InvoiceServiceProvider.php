<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Invoice\src\Application\Listeners\LogInvoiceAuditListener;
use Modules\Invoice\src\Application\Listeners\SendInvoiceCreatedNotificationListener;
use Modules\Invoice\src\Application\Services\InvoiceService;
use Modules\Invoice\src\Domain\Events\InvoiceCreated;
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
                $app->make(ServiceRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        // Events
        Event::listen(
            InvoiceCreated::class,
            LogInvoiceAuditListener::class
        );

        Event::listen(
            InvoiceCreated::class,
            SendInvoiceCreatedNotificationListener::class
        );
    }
}
