<?php

declare(strict_types=1);

namespace Modules\Account\Application\Services;

use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Modules\Account\Domain\Entities\Account;
use Modules\Account\Infrastructure\Repositories\AccountRepository;

class AccountService
{
    protected AccountRepository $repository;

    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all accounts
     */
    public function getAllAccounts(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get accounts by ID
     */
    public function getAccountById(int $id): Account
    {
        return $this->repository->findOrFail($id);
    }

    public function decrementBalance(int $id, string $amount): void
    {
        $account = $this->getAccountById($id);
        $amountValue = (float)$amount;

        if ($account->balance < $amountValue) {
            throw new DomainException('Insufficient funds');
        }

        $this->repository->decrementBalance($id, $amount);
    }

    public function incrementBalance(int $id, string $amount): void
    {
        $this->repository->incrementBalance($id, $amount);
    }

}
