<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Account;
use App\Models\Transaction;

class TransferRepository
{
    public function findAccountForUpdate(int $accountId): ?Account
    {
        // lockForUpdate - locking a row in the database for the duration of a transaction.
        return Account::lockForUpdate()->find($accountId);
    }

    public function updateAccountBalance(Account $account, float $newBalance): bool
    {
        $account->balance = $newBalance;
        return $account->save();
    }

    public function createTransferOut(
        int     $accountId,
        float   $amount,
        string  $toAccountNumber,
        ?string $description = null
    ): Transaction
    {
        return Transaction::create([
            'account_id' => $accountId,
            'amount' => -$amount,
            'type' => 'transfer_out',
            'status' => 'completed',
            'description' => "Переказ на рахунок {$toAccountNumber}. {$description}",
        ]);
    }

    public function createTransferIn(
        int     $accountId,
        float   $amount,
        string  $fromAccountNumber,
        ?string $description = null
    ): Transaction
    {
        return Transaction::create([
            'account_id' => $accountId,
            'amount' => $amount,
            'type' => 'transfer_in',
            'status' => 'completed',
            'description' => "Надходження з рахунку {$fromAccountNumber}. {$description}",
        ]);
    }
}
