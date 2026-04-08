<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendInvoiceCreatedNotificationListener implements ShouldQueue
{
    public function handle(InvoiceCreated $event): void
    {
        // temp log for 4 task
        Log::info('SendInvoiceCreatedNotificationListener: Invoice created', [
            'invoice_id' => $event->invoiceId,
            'client_id' => $event->clientId,
            'total_amount' => $event->totalAmount,
            'currency' => $event->currency,
        ]);
    }
}
