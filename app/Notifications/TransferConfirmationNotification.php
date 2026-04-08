<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransferConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int    $transactionId,
        private readonly string $amount,
        private readonly string $currency,
        private readonly bool   $isReceiver, // true = received, false = sent
    )
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isReceiver
            ? 'Надходження коштів'
            : 'Підтвердження переказу';

        $message = $this->isReceiver
            ? "На ваш рахунок надійшло {$this->amount} {$this->currency}."
            : "Ваш переказ на суму {$this->amount} {$this->currency} успішно виконано.";

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->action('Переглянути транзакції', url('/transactions'))
            ->line('Дякуємо, що користуєтесь нашим сервісом!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'is_receiver' => $this->isReceiver,
            'type' => $this->isReceiver ? 'transfer_in' : 'transfer_out',
            'created_at' => now()->toIso8601String(),
        ];
    }
}
