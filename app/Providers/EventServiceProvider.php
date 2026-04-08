<?php
declare(strict_types=1);

namespace App\Providers;

use App\Events\TransferCompleted;
use App\Listeners\LogTransferToAudit;
use App\Listeners\SendTransferConfirmationNotification;
use App\Listeners\UpdateDashboardCacheListener;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransferCompleted::class => [
            SendTransferConfirmationNotification::class,
            LogTransferToAudit::class,
            UpdateDashboardCacheListener::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
