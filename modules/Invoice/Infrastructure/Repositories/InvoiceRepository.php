<?php

declare(strict_types=1);

namespace Modules\Invoice\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Invoice\Domain\Entities\Invoice;
use Modules\Invoice\Domain\Repositories\InvoiceRepositoryInterface;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function getAll(): Collection
    {
        return Invoice::with('client')->orderBy('created_at', 'desc')->get();
    }

    public function findById(int $id): ?Invoice
    {
        return Invoice::find($id);
    }
}
