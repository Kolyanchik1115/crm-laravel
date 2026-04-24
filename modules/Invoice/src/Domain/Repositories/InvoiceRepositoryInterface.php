<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Invoice\src\Domain\Entities\Invoice;

interface InvoiceRepositoryInterface
{
    /**
     * @return Collection<int, Invoice>
     */
    public function getAll(): Collection;

    public function create(array $data): Invoice;

    public function findById(int $id): ?Invoice;
}
