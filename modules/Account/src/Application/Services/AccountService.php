<?php

declare(strict_types=1);

namespace Modules\Account\src\Application\Services;

use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Account\src\Infrastructure\Repositories\AccountRepository;

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

    public function getAllAccountsPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($perPage);
    }
}
