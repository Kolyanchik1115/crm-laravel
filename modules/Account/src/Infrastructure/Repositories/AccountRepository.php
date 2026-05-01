<?php

declare(strict_types=1);

namespace Modules\Account\src\Infrastructure\Repositories;

use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Account\src\Domain\Repositories\AccountRepositoryInterface;

class AccountRepository implements AccountRepositoryInterface
{
    /**
     * Get all accounts with their clients
     */
    public function getAll(): Collection
    {
        return Account::with('client')
            ->orderBy('account_number')
            ->get();
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Account::with(['client', 'transactions'])
            ->orderBy('account_number')
            ->paginate($perPage);
    }

    /**
     * Find accounts by ID with clients
     */
    public function findOrFail(int $id): Account
    {
        return Account::with('client')->findOrFail($id);
    }

    public function findById(int $id): ?Account
    {
        return Account::find($id);
    }

    public function decrementBalance(int $id, string $amount): void
    {
        $account = $this->findById($id);

        if (!$account) {
            throw new DomainException('Account not found');
        }

        $amountValue = (float)$amount;
        $account->balance -= $amountValue;
        $account->save();
    }

    public function incrementBalance(int $id, string $amount): void
    {
        $account = $this->findById($id);

        if (!$account) {
            throw new DomainException('Account not found');
        }

        $amountValue = (float)$amount;
        $account->balance += $amountValue;
        $account->save();
    }
}
