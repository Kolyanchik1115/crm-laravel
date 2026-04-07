<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendTransferConfirmationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public array $backoff = [5, 10, 15];

    public function __construct(
        private readonly int $transactionIdOut
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // exception for testing
        // throw new \RuntimeException('Simulated mail service failure');

        // Load transfer_out transaction
        $transactionOut = Transaction::with(['account.client'])
            ->where('type', 'transfer_out')
            ->find($this->transactionIdOut);

        if (!$transactionOut) {
            Log::warning('SendTransferConfirmationJob: Transaction not found', [
                'transaction_id' => $this->transactionIdOut,
            ]);
            return;
        }

        // Looking for transaction transfer_in
        $transactionIn = Transaction::with(['account.client'])
            ->where('type', 'transfer_in')
            ->where('description', 'LIKE', "%{$transactionOut->id}%")
            ->first();

        if (!$transactionIn) {
            Log::warning('SendTransferConfirmationJob: Transfer_in transaction not found', [
                'transaction_out_id' => $transactionOut->id,
            ]);
            return;
        }

        $sender = $transactionOut->account->client;
        $receiver = $transactionIn->account->client;
        $amount = abs($transactionOut->amount);
        $currency = $transactionOut->account->currency;

        $senderMessage = "Ваш переказ на суму {$amount} {$currency} на рахунок
        {$transactionIn->account->account_number} успішно виконано.";

        $receiverMessage = "На ваш рахунок {$transactionIn->account->account_number} надійшло
        {$amount} {$currency} від {$sender->full_name}.";

        Log::info('SendTransferConfirmationJob: Email to sender', [
            'email' => $sender->email,
            'subject' => 'Підтвердження переказу',
            'message' => $senderMessage,
        ]);

        Log::info('SendTransferConfirmationJob: Email to receiver', [
            'email' => $receiver->email,
            'subject' => 'Надходження коштів',
            'message' => $receiverMessage,
        ]);

        // Update transaction status
        $transactionOut->update(['status' => 'notification_sent']);
        $transactionIn->update(['status' => 'notification_sent']);
    }
}
