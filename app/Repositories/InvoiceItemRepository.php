<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InvoiceItem;

class InvoiceItemRepository
{
    public function create(array $data): InvoiceItem
    {
        return InvoiceItem::create($data);
    }

    public function createMany(int $invoiceId, array $items): void
    {
        foreach ($items as $item) {
            $this->create([
                'invoice_id' => $invoiceId,
                'service_id' => $item['service_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }
    }
}
