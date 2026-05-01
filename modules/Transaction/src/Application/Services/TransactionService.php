<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Transaction\src\Domain\Entities\Transaction;
use Modules\Transaction\src\Domain\Repositories\TransactionRepositoryInterface;

class TransactionService
{
    public function __construct(
        protected TransactionRepositoryInterface $repository
    ) {
    }

    public function getAllTransactions(): Collection
    {
        return $this->repository->getAll();
    }

    public function getAllTransactionsPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($perPage);
    }

    public function getTransactionById(int $id): Transaction
    {
        return $this->repository->findOrFail($id);
    }

    public function getAccountTransactionsPaginated(int $accountId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAccountTransactionsPaginated($accountId, $perPage);
    }
}
