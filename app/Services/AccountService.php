<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountRepository;
use DomainException;
use Illuminate\Database\Eloquent\Collection;

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
     * Get account by ID
     */
    public function getAccountById(int $id): Account
    {
        return $this->repository->findOrFail($id);
    }

    public function decrementBalance(int $id, string $amount): void
    {
        $account = $this->getAccountById($id);

        // bccomp -> compares numbers as strings with exact precision
        if (bccomp($account->balance, $amount, 2) < 0) {
            throw new DomainException('Insufficient funds');
        }

        $this->repository->decrementBalance($id, $amount);
    }

    public function incrementBalance(int $id, string $amount): void
    {
        $this->repository->incrementBalance($id, $amount);
    }

}
