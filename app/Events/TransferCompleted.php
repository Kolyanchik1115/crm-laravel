<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly int    $transactionOutId,
        public readonly int    $accountFromId,
        public readonly int    $accountToId,
        public readonly string $amount,
        public readonly string $currency,
    )
    {
    }
}
