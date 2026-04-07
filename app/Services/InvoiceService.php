<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\LogInvoiceAuditJob;
use App\Jobs\UpdateDashboardCacheJob;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceItemRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepository     $invoiceRepository,
        protected InvoiceItemRepository $invoiceItemRepository
    )
    {
    }

    public function createInvoice(int $clientId, array $items): array
    {
        $invoice = null;

        DB::transaction(function () use ($clientId, $items, &$invoice) {
            $total = collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);

            $invoice = $this->invoiceRepository->create([
                'client_id' => $clientId,
                'invoice_number' => $this->generateInvoiceNumber(),
                'total_amount' => $total,
                'status' => 'draft',
                'issued_at' => now(),
            ]);

            $this->invoiceItemRepository->createMany($invoice->id, $items);
        });

        // Async log writing
        LogInvoiceAuditJob::dispatch($invoice->id);

        // Cache update with 30 sec delay
        UpdateDashboardCacheJob::dispatch()->delay(now()->addSeconds(30));

        return [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total_amount' => $invoice->total_amount,
        ];
    }

    private function generateInvoiceNumber(): string
    {
        $lastInvoice = $this->invoiceRepository->getAll()->first();
        $lastNumber = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) : 0;
        $newNumber = $lastNumber + 1;

        return 'INV-' . date('Ymd') . '-' . str_pad((string) $newNumber,
                4, '0', STR_PAD_LEFT);
    }
}
