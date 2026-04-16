<?php

declare(strict_types=1);

namespace Modules\Transaction\Application\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Modules\Transaction\Domain\Entities\Transaction;
use Modules\Transaction\Infrastructure\Notifications\TransferConfirmationNotification;

class SendTransferConfirmationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;
    public array $backoff = [5, 10, 15];

    public function __construct(
        private readonly int $transactionIdOut
    ) {
    }

    public function handle(): void
    {
        try {
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

            // Check if not sent
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

            // Notification instead Mail::raw
            $sender->notify(new TransferConfirmationNotification(
                transactionId: $this->transactionIdOut,
                amount: (string)$amount,
                currency: $currency,
                isReceiver: false,
            ));

            $receiver->notify(new TransferConfirmationNotification(
                transactionId: $transactionIn->id,
                amount: (string)$amount,
                currency: $currency,
                isReceiver: true,
            ));

            Log::info('SendTransferConfirmationJob: Notifications sent', [
                'job' => self::class,
                'transaction_out_id' => $this->transactionIdOut,
                'sender_email' => $sender->email,
                'receiver_email' => $receiver->email,
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
