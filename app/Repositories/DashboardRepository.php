<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

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
