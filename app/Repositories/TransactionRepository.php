<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository
{
    /**
     * Get all transactions with account and client
     */
    public function getAll(): Collection
    {
        return Transaction::with('account.client')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
