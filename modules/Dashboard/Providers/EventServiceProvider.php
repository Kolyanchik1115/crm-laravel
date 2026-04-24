<?php

declare(strict_types=1);

namespace Modules\Dashboard\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Transaction\Domain\Events\TransferCompleted;
use Modules\Invoice\Domain\Events\InvoiceCreated;
use Modules\Dashboard\Application\Listeners\UpdateDashboardCacheListener;
use Modules\Dashboard\Application\Listeners\UpdateDashboardCacheOnInvoiceListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransferCompleted::class => [
            UpdateDashboardCacheListener::class,
        ],
        InvoiceCreated::class => [
            UpdateDashboardCacheOnInvoiceListener::class,
        ],
    ];
}
