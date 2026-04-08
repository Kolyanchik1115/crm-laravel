<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\TransferCompleted;
use App\Repositories\TransferRepository;
use Illuminate\Support\Facades\DB;

class TransferService
{
    protected TransferRepository $repository;

    public function __construct(TransferRepository $repository)
    {
        $this->repository = $repository;
    }

    public function executeTransfer(
        int     $fromAccountId,
        int     $toAccountId,
        float   $amount,
        ?string $description = null
    ): array
    {
        $transactionOut = null;
        $transactionIn = null;
        $fromAccount = null;
        $toAccount = null;

        DB::transaction(function () use (
            $fromAccountId, $toAccountId, $amount, $description,
            &$transactionOut, &$transactionIn, &$fromAccount, &$toAccount
        ) {
            $fromAccount = $this->repository->findAccountForUpdate($fromAccountId);
            $toAccount = $this->repository->findAccountForUpdate($toAccountId);

            if (!$fromAccount || !$toAccount) {
                throw new \Exception('Рахунок не знайдено');
            }

            if ($fromAccount->balance < $amount) {
                throw new \Exception('Недостатньо коштів');
            }

            $this->repository->updateAccountBalance($fromAccount, $fromAccount->balance - $amount);
            $this->repository->updateAccountBalance($toAccount, $toAccount->balance + $amount);

            $transactionOut = $this->repository->createTransferOut(
                $fromAccount->id,
                $amount,
                $toAccount->account_number,
                $description
            );

            $transactionIn = $this->repository->createTransferIn(
                $toAccount->id,
                $amount,
                $fromAccount->account_number,
                $description,
                $transactionOut->id
            );
        });


        // Action instead job
        event(new TransferCompleted(
            transactionOutId: $transactionOut->id,
            accountFromId: $fromAccount->id,
            accountToId: $toAccount->id,
            amount: (string)$amount,
            currency: $fromAccount->currency,
        ));

        //  Dispatch Job after success transfer
        //  SendTransferConfirmationJob::dispatch($transactionOut->id, $transactionIn->id)
        //    ->onQueue('notifications');
        //  Cache update with 30 sec delay
        //  UpdateDashboardCacheJob::dispatch()
        //   ->onQueue('low')
        //    ->delay(now()->addSeconds(30));

        return [
            'transaction_out_id' => $transactionOut->id,
            'transaction_in_id' => $transactionIn->id,
            'amount' => $amount,
        ];
    }
}

