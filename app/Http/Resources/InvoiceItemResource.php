<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $invoice_id
 * @property int $service_id
 * @property int $quantity
 * @property float $unit_price
 * @property-read Invoice $invoice
 * @property-read Service $service
 */
class InvoiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'invoice_id' => $this->invoice_id,
            'service_id' => $this->service_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total' => (float)($this->quantity * $this->unit_price),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
        ];
    }
}
