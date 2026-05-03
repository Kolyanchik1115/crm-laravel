<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Invoice\src\Domain\Entities\Invoice;
use Modules\Invoice\src\Domain\Repositories\InvoiceRepositoryInterface;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function findById(int $id): ?Invoice
    {
        return Invoice::find($id);
    }

    public function findOrFail(int $id): Invoice
    {
        return Invoice::with(['client', 'items.service'])->findOrFail($id);
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::with(['client', 'items.service'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getMaxId(): ?int
    {
        return Invoice::max('id');
    }

    public function updateStatus(int $id, string $status): Invoice
    {
        $invoice = $this->findOrFail($id);
        $invoice->update(['status' => $status]);
        return $invoice;
    }


}
