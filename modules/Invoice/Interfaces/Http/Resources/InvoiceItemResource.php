<?php

declare(strict_types=1);

namespace Modules\Invoice\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Invoice\Domain\Entities\Invoice;
use Modules\Service\Domain\Entities\Service;
use Modules\Service\Interfaces\Http\Resources\ServiceResource;

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
