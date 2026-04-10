<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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
