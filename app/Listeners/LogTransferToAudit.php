<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TransferCompleted;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogTransferToAudit implements ShouldQueue
{
    public function handle(TransferCompleted $event): void
    {
        $payload = [
            'amount' => $event->amount,
            'currency' => $event->currency,
            'account_from_id' => $event->accountFromId,
            'account_to_id' => $event->accountToId,
        ];

        AuditLog::updateOrCreate(
            [
                'entity_id' => $event->transactionOutId,
                'event_type' => 'transfer_completed',
            ],
            [
                'entity_type' => 'transaction',
                'payload' => $payload,
            ]
        );
    }
}
