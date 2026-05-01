<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Invoice\src\Application\Listeners\LogInvoiceAuditListener;
use Modules\Invoice\src\Application\Listeners\SendInvoiceCreatedNotificationListener;
use Modules\Invoice\src\Domain\Events\InvoiceCreated;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        InvoiceCreated::class => [
            LogInvoiceAuditListener::class,
            SendInvoiceCreatedNotificationListener::class,
        ],
    ];
}
