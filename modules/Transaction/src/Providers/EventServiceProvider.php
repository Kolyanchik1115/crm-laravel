<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Transaction\src\Application\Listeners\LogTransferToAudit;
use Modules\Transaction\src\Application\Listeners\SendTransferConfirmationNotification;
use Modules\Transaction\src\Domain\Events\TransferCompleted;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransferCompleted::class => [
            SendTransferConfirmationNotification::class,
            LogTransferToAudit::class,
        ],
    ];
}
