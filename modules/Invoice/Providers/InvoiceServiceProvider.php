<?php

declare(strict_types=1);

namespace Modules\Invoice\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Invoice\Application\Services\InvoiceService;
use Modules\Invoice\Domain\Repositories\InvoiceItemRepositoryInterface;
use Modules\Invoice\Infrastructure\Repositories\InvoiceItemRepository;
use Modules\Invoice\Infrastructure\Repositories\InvoiceRepository;
use Modules\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoice\Domain\Events\InvoiceCreated;
use Modules\Invoice\Application\Listeners\LogInvoiceAuditListener;
use Modules\Invoice\Application\Listeners\SendInvoiceCreatedNotificationListener;
use Modules\Service\Domain\Repositories\ServiceRepositoryInterface;

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
