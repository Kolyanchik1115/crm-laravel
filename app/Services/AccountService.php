<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountRepository;
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
}
