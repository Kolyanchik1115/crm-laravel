<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Transaction\src\Domain\Entities\Transaction;
use Modules\Transaction\src\Domain\Repositories\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getAll(): Collection
    {
        return Transaction::with('account.client')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with('account.client')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function findById(int $id): ?Transaction
    {
        return Transaction::find($id);
    }

    public function findOrFail(int $id): Transaction
    {
        return Transaction::with('account.client')->findOrFail($id);
    }

    public function getAccountTransactionsPaginated(int $accountId, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['account'])
            ->where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findAccountForUpdate(int $accountId): ?Account
    {
        return Account::lockForUpdate()->find($accountId);
    }

    public function updateAccountBalance(Account $account, float $newBalance): bool
    {
        $account->balance = $newBalance;
        return $account->save();
    }

    public function createTransferOut(int $accountId, float $amount, string $toAccountNumber,
                                      ?string $description = null): Transaction
    {
        return Transaction::create([
            'account_id' => $accountId,
            'amount' => -$amount,
            'type' => 'transfer_out',
            'status' => 'completed',
            'description' => "Переказ на рахунок {$toAccountNumber}. {$description}",
        ]);
    }

    public function createTransferIn(int $accountId, float $amount, string $fromAccountNumber,
                                     ?string $description = null, ?int $transactionOutId = null): Transaction
    {
        $desc = "Надходження з рахунку {$fromAccountNumber}. {$description}";
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

    public function getTransfersPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['account'])
            ->where('type', 'transfer_out')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getTransferById(int $id): Transaction
    {
        return Transaction::with(['account'])
            ->where('type', 'transfer_out')
            ->findOrFail($id);
    }
}
