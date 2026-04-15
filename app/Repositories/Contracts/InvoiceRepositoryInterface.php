<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepositoryInterface
{
    /**
     * @return Collection<int, Invoice>
     */
    public function getAll(): Collection;

    public function create(array $data): Invoice;

    public function findById(int $id): ?Invoice;
}
