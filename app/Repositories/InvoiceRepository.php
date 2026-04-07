<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

class InvoiceRepository
{
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function getAll(): Collection
    {
        return Invoice::with('client')->orderBy('created_at', 'desc')->get();
    }
}
