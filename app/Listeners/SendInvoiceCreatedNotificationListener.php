<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Notifications\InvoiceCreatedNotification;
use Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendInvoiceCreatedNotificationListener implements ShouldQueue
{
    public function handle(InvoiceCreated $event): void
    {
        /** @var Client|null $client */
        $client = Client::find($event->clientId);

        if ($client === null) {
            Log::warning('SendInvoiceCreatedNotificationListener: Client not found', [
                'client_id' => $event->clientId,
            ]);
            return;
        }

        // Sent with 3 sec delay
        $client->notify(
            (new InvoiceCreatedNotification(
                invoiceId: $event->invoiceId,
                totalAmount: $event->totalAmount,
                currency: $event->currency,
            ))->delay(now()->addMinutes(3))
        );

        Log::info('SendInvoiceCreatedNotificationListener: Notification queued with delay', [
            'invoice_id' => $event->invoiceId,
            'client_id' => $event->clientId,
            'delay_minutes' => 3,
        ]);
    }
}
