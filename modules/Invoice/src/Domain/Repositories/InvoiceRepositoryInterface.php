<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Invoice\src\Domain\Entities\Invoice;

interface InvoiceRepositoryInterface
{
    public function create(array $data): Invoice;

    public function findById(int $id): ?Invoice;

    public function findOrFail(int $id): Invoice;

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function getMaxId(): ?int;

    public function updateStatus(int $id, string $status): Invoice;

}
