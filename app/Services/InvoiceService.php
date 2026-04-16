<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CreateInvoiceDTO;
use App\Events\InvoiceCreated;
use App\Jobs\LogInvoiceAuditJob;
use App\Jobs\UpdateDashboardCacheJob;
use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceItemRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceItemRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepositoryInterface     $invoiceRepository,
        protected InvoiceItemRepositoryInterface $invoiceItemRepository,
        protected ServiceRepositoryInterface     $serviceRepository,
    ) {
    }

    public function createInvoice(CreateInvoiceDTO $dto): Invoice
    {
        // exists service checking
        foreach ($dto->items as $invoiceItem) {
            if (!$this->serviceRepository->exists($invoiceItem->serviceId)) {
                throw new \DomainException("Services with ID {$invoiceItem->serviceId} not found");
            }
        }

        $createdInvoice = null;

        DB::transaction(function () use ($dto, &$createdInvoice) {
            $invoiceTotalAmount = collect($dto->items)->sum(
                fn ($item) => $item->quantity * $item->unitPrice
            );

            $createdInvoice = $this->invoiceRepository->create([
                'client_id' => $dto->clientId,
                'invoice_number' => $this->generateInvoiceNumber(),
                'total_amount' => $invoiceTotalAmount,
                'status' => 'draft',
                'issued_at' => now(),
            ]);

            $this->invoiceItemRepository->createMany($createdInvoice->id, $dto->items);
        });

        // event invoice created instead jobs
        event(new InvoiceCreated(
            invoiceId: $createdInvoice->id,
            clientId: $dto->clientId,
            totalAmount: (string)$createdInvoice->total_amount,
            currency: $dto->currency,
        ));

        // // Async log writing
        // LogInvoiceAuditJob::dispatch($invoice->id)->onQueue('audit');
        //
        // // Cache update with 30 sec delay
        // UpdateDashboardCacheJob::dispatch()->onQueue('low')->delay(now()->addSeconds(30));

        return $createdInvoice;
    }

    private function generateInvoiceNumber(): string
    {
        $lastInvoice = $this->invoiceRepository->findById(Invoice::max('id'));
        $lastNumber = $lastInvoice ? (int)substr($lastInvoice->invoice_number, -4) : 0;
        $newNumber = $lastNumber + 1;

        return 'INV-' . date('Ymd') . '-' . str_pad(
            (string)$newNumber,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
