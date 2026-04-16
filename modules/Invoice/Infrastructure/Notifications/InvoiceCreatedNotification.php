<?php

declare(strict_types=1);

namespace Modules\Invoice\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int    $invoiceId,
        private readonly string $totalAmount,
        private readonly string $currency,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Новий рахунок-фактура')
            ->line("Рахунок №{$this->invoiceId} на суму {$this->totalAmount} {$this->currency} створено.")
            ->line('Будь ласка, здійсніть оплату в найближчий час.')
            ->action('Переглянути рахунок', url("/invoices/{$this->invoiceId}"))
            ->line('Дякуємо, що користуєтесь нашим сервісом!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoiceId,
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'type' => 'invoice_created',
            'created_at' => now()->toIso8601String(),
        ];
    }
}
