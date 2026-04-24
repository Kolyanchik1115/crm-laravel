<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Application\Jobs;

use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Modules\Invoice\src\Domain\Entities\Invoice;

class LogInvoiceAuditJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [5, 15, 60];

    public function __construct(
        private readonly int    $invoiceId,
        private readonly ?int   $userId = null,
        private readonly string $eventType = 'invoice_created'
    ) {
    }

    public function handle(): void
    {
        try {

            // invoice with clients and items
            $invoice = Invoice::with(['client', 'items'])->find($this->invoiceId);

            if (!$invoice) {
                Log::warning('LogInvoiceAuditJob: Invoice not found', [
                    'job' => self::class,
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
                'job' => self::class,
                'invoice_id' => $this->invoiceId,
                'client_name' => $invoice->client->full_name,
                'total_amount' => $invoice->total_amount,
                'items_count' => $invoice->items->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('LogInvoiceAuditJob: Failed', [
                'job' => self::class,
                'invoice_id' => $this->invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
