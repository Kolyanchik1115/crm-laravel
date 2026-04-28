<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\TransferDTO;
use App\Events\TransferCompleted;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SameAccountTransferException;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Traits\CorrelationIdTrait;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService
{
    use CorrelationIdTrait;
    private const float COMMISSION_RATE = 0.005; // 0.5%
    private const float COMMISSION_THRESHOLD = 10000;

    public function __construct(
        protected AccountRepositoryInterface     $accountRepository,
        protected TransactionRepositoryInterface $transactionRepository,
    ) {
    }

    public function executeTransfer(TransferDTO $dto): array
    {
        $correlationId = $this->getCorrelationId();

        // check if account is not same
        if ($dto->accountFromId === $dto->accountToId) {
            Log::warning('Transfer failed: same account', [
                'account_from_id' => $dto->accountFromId,
                'account_to_id' => $dto->accountToId,
                'correlation_id' => $correlationId,
            ]);
            throw new SameAccountTransferException();
        }

        $transactionOut = null;
        $transactionIn = null;
        $fromAccount = null;
        $toAccount = null;
        $commission = 0;

        DB::transaction(function () use (
            $dto,
            &$transactionOut,
            &$transactionIn,
            &$fromAccount,
            &$toAccount,
            &$commission,
            &$correlationId
        ) {
            $fromAccount = $this->accountRepository->findById($dto->accountFromId);
            $toAccount = $this->accountRepository->findById($dto->accountToId);

            if (!$fromAccount || !$toAccount) {
                Log::warning('Transfer failed: account not found', [
                    'account_from_id' => $dto->accountFromId,
                    'account_to_id' => $dto->accountToId,
                    'correlation_id' => $correlationId,
                ]);
                throw new \DomainException('Рахунок не знайдено');
            }

            $amount = $dto->amount->getValue();

            $commission = $this->calculateCommission($dto->amount);
            $totalDeduct = $dto->amount->getValue() + $commission;

            // check if balance is enough
            if ($fromAccount->balance < $totalDeduct) {
                Log::warning('Transfer failed: insufficient balance', [
                    'account_from_id' => $fromAccount->id,
                    'amount' => $amount,
                    'balance' => $fromAccount->balance,
                    'commission' => $commission,
                    'total_deduct' => $totalDeduct,
                    'correlation_id' => $correlationId,
                ]);
                throw new InsufficientBalanceException();
            }

            // new decrement and increment functions from repository
            $this->accountRepository->decrementBalance($dto->accountFromId, (string)$totalDeduct);
            $this->accountRepository->incrementBalance($dto->accountToId, (string)$amount);

            $transactionOut = $this->transactionRepository->create([
                'account_id' => $fromAccount->id,
                'amount' => -$amount,
                'type' => 'transfer_out',
                'status' => 'completed',
                'description' => "Переказ на рахунок {$toAccount->account_number}.
                 Комісія: {$commission}. {$dto->description}",
            ]);

            $transactionIn = $this->transactionRepository->create([
                'account_id' => $toAccount->id,
                'amount' => $amount,
                'type' => 'transfer_in',
                'status' => 'completed',
                'description' => "Надходження з рахунку {$fromAccount->account_number}. {$dto->description}",
            ]);
        });

        Log::info('Transfer completed successfully', [
            'transfer_out_id' => $transactionOut->id,
            'transfer_in_id' => $transactionIn->id,
            'account_from_id' => $fromAccount->id,
            'account_to_id' => $toAccount->id,
            'amount' => $dto->amount->getValue(),
            'currency' => $dto->amount->currency,
            'commission' => $commission,
            'correlation_id' => $correlationId,
        ]);

        // transfer completed event
        event(new TransferCompleted(
            transactionOutId: $transactionOut->id,
            accountFromId: $fromAccount->id,
            accountToId: $toAccount->id,
            amount: (string)$dto->amount->getValue(),
            currency: $dto->amount->currency,
        ));

        return [
            'transaction_out_id' => $transactionOut->id,
            'transaction_in_id' => $transactionIn->id,
            'account_from_id' => $fromAccount->id,
            'account_to_id' => $toAccount->id,
            'amount' => $dto->amount->getValue(),
            'commission' => $commission,
        ];
    }

    // function for calculate commission
    private function calculateCommission(Money $amount): float
    {
        if ($amount->isGreaterThan((string)self::COMMISSION_THRESHOLD)) {
            return round($amount->getValue() * self::COMMISSION_RATE, 2);
        }
        return 0;
    }
}
