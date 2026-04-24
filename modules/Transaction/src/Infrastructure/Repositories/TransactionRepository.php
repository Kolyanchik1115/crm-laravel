<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Transaction\src\Domain\Entities\Transaction;
use Modules\Transaction\src\Domain\Repositories\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Get all transactions with accounts and clients
     */
    public function getAll(): Collection
    {
        return Transaction::with('account.client')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function findById(int $id): ?Transaction
    {
        return Transaction::find($id);
    }
}
