<?php

declare(strict_types=1);

namespace Modules\Transaction\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Transaction\Application\Jobs\SendTransferConfirmationJob;
use Modules\Transaction\Domain\Events\TransferCompleted;

class SendTransferConfirmationNotification implements ShouldQueue
{
    public function handle(TransferCompleted $event): void
    {
        SendTransferConfirmationJob::dispatch($event->transactionOutId)
            ->onQueue('notifications');
    }
}
