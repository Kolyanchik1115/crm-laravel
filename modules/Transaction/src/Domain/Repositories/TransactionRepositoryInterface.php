<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Transaction\src\Domain\Entities\Transaction;

interface TransactionRepositoryInterface
{
    public function getAll(): Collection;
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Transaction;
    public function findById(int $id): ?Transaction;
    public function findOrFail(int $id): Transaction;
    public function getTransfersPaginated(int $perPage = 15): LengthAwarePaginator;
    public function getTransferById(int $id): Transaction;
    public function getAccountTransactionsPaginated(int $accountId, int $perPage = 15): LengthAwarePaginator;
    public function findAccountForUpdate(int $accountId): ?Account;
    public function updateAccountBalance(Account $account, float $newBalance): bool;
    public function createTransferOut(int $accountId, float $amount, string $toAccountNumber,
                                      ?string $description = null): Transaction;
    public function createTransferIn(int $accountId, float $amount, string $fromAccountNumber,
                                     ?string $description = null, ?int $transactionOutId = null): Transaction;
}
