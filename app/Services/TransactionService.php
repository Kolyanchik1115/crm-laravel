<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\TransactionRepository;
use Illuminate\Database\Eloquent\Collection;

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
