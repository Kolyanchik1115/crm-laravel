<?php

declare(strict_types=1);

namespace Modules\Dashboard\src\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Dashboard\src\Application\Listeners\UpdateDashboardCacheListener;
use Modules\Dashboard\src\Application\Listeners\UpdateDashboardCacheOnInvoiceListener;
use Modules\Invoice\src\Domain\Events\InvoiceCreated;
use Modules\Transaction\src\Domain\Events\TransferCompleted;

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
