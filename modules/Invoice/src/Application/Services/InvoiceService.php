<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Application\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Invoice\src\Application\DTO\CreateInvoiceDTO;
use Modules\Invoice\src\Application\Services\Monitoring\InvoiceErrorReporter;
use Modules\Invoice\src\Domain\Entities\Invoice;
use Modules\Invoice\src\Domain\Events\InvoiceCreated;
use Modules\Invoice\src\Domain\Repositories\InvoiceItemRepositoryInterface;
use Modules\Invoice\src\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Service\src\Domain\Repositories\ServiceRepositoryInterface;
use Modules\Shared\src\Domain\Traits\CorrelationIdTrait;

class InvoiceService
{
    use CorrelationIdTrait;
    public function __construct(
        protected InvoiceRepositoryInterface     $invoiceRepository,
        protected InvoiceItemRepositoryInterface $invoiceItemRepository,
        protected ServiceRepositoryInterface     $serviceRepository,
        protected InvoiceErrorReporter           $errorReporter,
    ) {
    }

    public function createInvoice(CreateInvoiceDTO $dto): Invoice
    {
        $correlationId = $this->getCorrelationId();

        // exists service checking
        foreach ($dto->items as $invoiceItem) {
            if (!$this->serviceRepository->exists($invoiceItem->serviceId)) {
                Log::warning('Invoice creation failed: service not found', [
                    'service_id' => $invoiceItem->serviceId,
                    'client_id' => $dto->clientId,
                    'correlation_id' => $correlationId,
                ]);
                throw new \DomainException("Service with ID {$invoiceItem->serviceId} not found");
            }
        }

        $createdInvoice = null;
        try {
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
        } catch (\Throwable $e) {
            $this->errorReporter->report(
                e: $e,
                clientId: $dto->clientId,
                invoiceId: $createdInvoice->id ?? null,
                totalAmount: isset($createdInvoice) ? (string)$createdInvoice->total_amount : null,
                correlationId: $correlationId,
            );
            throw $e;
        }

        Log::info('Invoice created successfully', [
            'invoice_id' => $createdInvoice->id,
            'client_id' => $dto->clientId,
            'total_amount' => (float)$createdInvoice->total_amount,
            'items_count' => count($dto->items),
            'currency' => $dto->currency,
            'status' => $createdInvoice->status,
            'correlation_id' => $correlationId,
        ]);

        event(new InvoiceCreated(
            invoiceId: $createdInvoice->id,
            clientId: $dto->clientId,
            totalAmount: (string)$createdInvoice->total_amount,
            currency: $dto->currency,
        ));

        return $createdInvoice;
    }

    public function getInvoiceById(int $id): Invoice
    {
        return $this->invoiceRepository->findOrFail($id);
    }

    public function getAllInvoicesPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->invoiceRepository->getAllPaginated($perPage);
    }

    private function generateInvoiceNumber(): string
    {
        $maxId = Invoice::max('id');

        if ($maxId === null) {
            return 'INV-' . date('Ymd') . '-0001';
        }

        $lastInvoice = $this->invoiceRepository->findById($maxId);

        $lastNumber = $lastInvoice
            ? (int)substr($lastInvoice->invoice_number, -4)
            : 0;

        $newNumber = $lastNumber + 1;

        return 'INV-' . date('Ymd') . '-' . str_pad(
            (string)$newNumber,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
