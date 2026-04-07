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
        try {
            // exception for testing
            // throw new \RuntimeException('Simulated mail service failure');

            // Load transfer_out transaction
            $transactionOut = Transaction::with(['account.client'])
                ->where('type', 'transfer_out')
                ->find($this->transactionIdOut);

            if (!$transactionOut) {
                Log::warning('SendTransferConfirmationJob: Transaction not found', [
                    'job' => self::class,
                    'transaction_id' => $this->transactionIdOut,
                ]);
                return;
            }

            //check if not sent
            if ($transactionOut->confirmation_sent_at) {
                Log::info('SendTransferConfirmationJob: Skip - confirmation already sent', [
                    'job' => self::class,
                    'transaction_out_id' => $this->transactionIdOut,
                    'sent_at' => $transactionOut->confirmation_sent_at,
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
                    'job' => self::class,
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

            Log::info('SendTransferConfirmationJob: Sending email to sender', [
                'job' => self::class,
                'transaction_out_id' => $this->transactionIdOut,
                'email' => $sender->email,
                'amount' => $amount,
            ]);

            Log::info('SendTransferConfirmationJob: Sending email to receiver', [
                'job' => self::class,
                'transaction_in_id' => $transactionIn->id,
                'email' => $receiver->email,
                'amount' => $amount,
            ]);

            // Update transaction status
            $transactionOut->update([
                'status' => 'notification_sent',
                'confirmation_sent_at' => now(),
            ]);

            $transactionIn->update([
                'status' => 'notification_sent',
            ]);

            Log::info('SendTransferConfirmationJob: Confirmation sent and marked', [
                'job' => self::class,
                'transaction_out_id' => $transactionOut->id,
                'transaction_in_id' => $transactionIn->id,
            ]);
        } catch (\Exception $e) {
            Log::error('SendTransferConfirmationJob: Failed', [
                'job' => self::class,
                'transaction_out_id' => $this->transactionIdOut,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
