<?php

declare(strict_types=1);

namespace Modules\Transaction\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Transaction\Domain\Entities\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * @return Collection<int, Transaction>
     */
    public function getAll(): Collection;
    public function create(array $data): Transaction;

    public function findById(int $id): ?Transaction;
}
