<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class CreateInvoiceDTO
{
    public function __construct(
        public int    $clientId,
        public array  $items,
        public string $currency = 'UAH',
    )
    {
    }

    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
            'currency' => $this->currency,
        ];
    }
}
