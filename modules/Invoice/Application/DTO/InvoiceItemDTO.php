<?php

declare(strict_types=1);

namespace Modules\Invoice\Application\DTO;

final readonly class InvoiceItemDTO
{
    public function __construct(
        public int   $serviceId,
        public int   $quantity,
        public float $unitPrice,
    ) {
    }

    public function toArray(): array
    {
        return [
            'service_id' => $this->serviceId,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
        ];
    }
}
