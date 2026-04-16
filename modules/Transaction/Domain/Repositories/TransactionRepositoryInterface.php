<?php

declare(strict_types=1);

namespace Modules\Transaction\Domain\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface
{
    /**
     * @return Collection<int, Transaction>
     */
    public function getAll(): Collection;
    public function create(array $data): Transaction;

    public function findById(int $id): ?Transaction;
}
