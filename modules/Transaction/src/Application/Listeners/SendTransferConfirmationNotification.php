<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Transaction\src\Application\Jobs\SendTransferConfirmationJob;
use Modules\Transaction\src\Domain\Events\TransferCompleted;

class SendTransferConfirmationNotification implements ShouldQueue
{
    public function handle(TransferCompleted $event): void
    {
        SendTransferConfirmationJob::dispatch($event->transactionOutId)
            ->onQueue('notifications');
    }
}
