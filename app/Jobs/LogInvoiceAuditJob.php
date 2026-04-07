<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class LogInvoiceAuditJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [5, 15, 60];

    public function __construct(
        private readonly int    $invoiceId,
        private readonly ?int   $userId = null,
        private readonly string $eventType = 'invoice_created'
    )
    {
    }

    public function handle(): void
    {
        // invoice with client and items
        $invoice = Invoice::with(['client', 'items'])->find($this->invoiceId);

        if (!$invoice) {
            Log::warning('LogInvoiceAuditJob: Invoice not found', [
                'invoice_id' => $this->invoiceId,
            ]);
            return;
        }

        //  payload
        $payload = [
            'total_amount' => $invoice->total_amount,
            'items_count' => $invoice->items->count(),
            'client_name' => $invoice->client->full_name,
        ];

        AuditLog::updateOrCreate(
            [
                'invoice_id' => $this->invoiceId,
                'event_type' => $this->eventType,
            ],
            [
                'entity_type' => 'invoice',
                'entity_id' => $invoice->id,
                'payload' => $payload,
                'user_id' => $this->userId,
            ]
        );

        Log::info('LogInvoiceAuditJob: Audit record created', [
            'invoice_id' => $this->invoiceId,
            'client_name' => $invoice->client->full_name,
            'total_amount' => $invoice->total_amount,
            'items_count' => $invoice->items->count(),
        ]);
    }
}
