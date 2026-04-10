<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Invoice;

interface InvoiceRepositoryInterface
{
    public function create(array $data): Invoice;
    public function findById(int $id): ?Invoice;
}
