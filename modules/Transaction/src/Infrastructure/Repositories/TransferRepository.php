<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Infrastructure\Repositories;

use Modules\Account\src\Domain\Entities\Account;
use Modules\Transaction\src\Domain\Entities\Transaction;

class TransferRepository
{
    /**
     * @return Account|null
     */
    public function findAccountForUpdate(int $accountId): ?Account
    {
        // lockForUpdate - locking a row in the database for the duration of a transaction
        /** @var Account|null $account */
        $account = Account::lockForUpdate()->find($accountId);
        return $account;
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
    ): Transaction {
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
        ?string $description = null,
        ?int    $transactionOutId = null
    ): Transaction {

        $desc = "Надходження з рахунку {$fromAccountNumber}. {$description}";

        // added this id due to error with search transaction ( temporary solution for example )
        if ($transactionOutId) {
            $desc .= " (transfer_out_id: {$transactionOutId})";
        }

        return Transaction::create([
            'account_id' => $accountId,
            'amount' => $amount,
            'type' => 'transfer_in',
            'status' => 'completed',
            'description' => $desc,
        ]);
    }
}
