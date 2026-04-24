<?php

declare(strict_types=1);

namespace Modules\Transaction\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Transaction\Domain\Events\TransferCompleted;
use Modules\Transaction\Application\Listeners\SendTransferConfirmationNotification;
use Modules\Transaction\Application\Listeners\LogTransferToAudit;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransferCompleted::class => [
            SendTransferConfirmationNotification::class,
            LogTransferToAudit::class,
        ],
    ];
}
