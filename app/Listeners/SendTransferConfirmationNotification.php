<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TransferCompleted;
use App\Jobs\SendTransferConfirmationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTransferConfirmationNotification implements ShouldQueue
{
    public function handle(TransferCompleted $event): void
    {
        SendTransferConfirmationJob::dispatch($event->transactionOutId)
            ->onQueue('notifications');
    }
}
