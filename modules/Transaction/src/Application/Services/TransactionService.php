<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Transaction\src\Infrastructure\Repositories\TransactionRepository;

class TransactionService
{
    protected TransactionRepository $repository;

    public function __construct(TransactionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all transactions
     */
    public function getAllTransactions(): Collection
    {
        return $this->repository->getAll();
    }
}
