<?php

declare(strict_types=1);

namespace Modules\Transaction\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Transaction\Infrastructure\Repositories\TransactionRepository;

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
