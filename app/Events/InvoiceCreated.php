<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int    $invoiceId,
        public readonly int    $clientId,
        public readonly string $totalAmount,
        public readonly string $currency,
    ) {
    }
}
