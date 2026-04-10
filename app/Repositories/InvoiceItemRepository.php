<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InvoiceItem;
use App\Repositories\Contracts\InvoiceItemRepositoryInterface;

class InvoiceItemRepository implements InvoiceItemRepositoryInterface
{
    public function create(array $data): InvoiceItem
    {
        return InvoiceItem::create($data);
    }

    public function createMany(int $invoiceId, array $items): void
    {
        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoiceId,
                'service_id' => $item->serviceId,
                'quantity' => $item->quantity,
                'unit_price' => $item->unitPrice,
            ]);
        }
    }
}
