<?php

declare(strict_types=1);

namespace Modules\Dashboard\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Client\Domain\Entities\Client;
use Modules\Transaction\Domain\Entities\Transaction;

class DashboardRepository
{
    /**
     * Get all clients with their accounts
     *
     * @return Collection<int, Client>
     */
    public function getAllClientsWithAccounts(): Collection
    {
        return Client::with('accounts')->get();
    }

    /**
     * Get all transactions
     */
    public function getAllTransactions(): Collection
    {
        return Transaction::all();
    }
}
