<?php

namespace App\Providers;

use App\Events\InvoiceCreated;
use App\Events\TransferCompleted;
use App\Listeners\LogInvoiceAuditListener;
use App\Listeners\LogTransferToAudit;
use App\Listeners\SendInvoiceCreatedNotificationListener;
use App\Listeners\SendTransferConfirmationNotification;
use App\Listeners\UpdateDashboardCacheListener;
use App\Listeners\UpdateDashboardCacheOnInvoiceListener;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransferCompleted::class => [
            SendTransferConfirmationNotification::class,
            LogTransferToAudit::class,
            UpdateDashboardCacheListener::class,
        ],
        InvoiceCreated::class => [
            LogInvoiceAuditListener::class,
            UpdateDashboardCacheOnInvoiceListener::class,
            SendInvoiceCreatedNotificationListener::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
