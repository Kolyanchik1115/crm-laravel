<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\TransferDTO;
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

    public function executeTransfer(TransferDTO $dto): array
    {
        $transactionOut = null;
        $transactionIn = null;
        $fromAccount = null;
        $toAccount = null;

        DB::transaction(function () use ($dto, &$transactionOut, &$transactionIn, &$fromAccount, &$toAccount) {
            $fromAccount = $this->repository->findAccountForUpdate($dto->accountFromId);
            $toAccount = $this->repository->findAccountForUpdate($dto->accountToId);

            if (!$fromAccount || !$toAccount) {
                throw new \Exception('Рахунок не знайдено');
            }

            $amountValue = $dto->amount->getValue();

            if ($fromAccount->balance < $amountValue) {
                throw new \Exception('Недостатньо коштів');
            }


            $this->repository->updateAccountBalance($fromAccount, $fromAccount->balance - $amountValue);
            $this->repository->updateAccountBalance($toAccount, $toAccount->balance + $amountValue);

            $transactionOut = $this->repository->createTransferOut(
                $fromAccount->id,
                $amountValue,
                $toAccount->account_number,
                $dto->description
            );

            $transactionIn = $this->repository->createTransferIn(
                $toAccount->id,
                $amountValue,
                $fromAccount->account_number,
                $dto->description,
                $transactionOut->id
            );
        });


        // Action instead job
        event(new TransferCompleted(
            transactionOutId: $transactionOut->id,
            accountFromId: $fromAccount->id,
            accountToId: $toAccount->id,
            amount: (string)$dto->amount->getValue(),
            currency: $dto->amount->currency,
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
            'amount' => $dto->amount->getValue(),
        ];
    }
}

