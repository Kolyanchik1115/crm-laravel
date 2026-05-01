<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Application\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Invoice\src\Application\DTO\CreateInvoiceDTO;
use Modules\Invoice\src\Domain\Entities\Invoice;
use Modules\Invoice\src\Domain\Events\InvoiceCreated;
use Modules\Invoice\src\Domain\Repositories\InvoiceItemRepositoryInterface;
use Modules\Invoice\src\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Service\src\Domain\Repositories\ServiceRepositoryInterface;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepository,
        protected InvoiceItemRepositoryInterface $invoiceItemRepository,
        protected ServiceRepositoryInterface $serviceRepository,
    ) {}

    public function createInvoice(CreateInvoiceDTO $dto): Invoice
    {
        // Check services exist
        foreach ($dto->items as $invoiceItem) {
            if (!$this->serviceRepository->exists($invoiceItem->serviceId)) {
                throw new \DomainException("Service with ID {$invoiceItem->serviceId} not found");
            }
        }

        $createdInvoice = null;

        DB::transaction(function () use ($dto, &$createdInvoice) {
            $invoiceTotalAmount = collect($dto->items)->sum(
                fn($item) => $item->quantity * $item->unitPrice
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
