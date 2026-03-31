<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

class AccountRepository
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

    /**
     * Find account by ID with client
     */
    public function findOrFail(int $id): Account
    {
        return Account::with('client')->findOrFail($id);
    }
}
