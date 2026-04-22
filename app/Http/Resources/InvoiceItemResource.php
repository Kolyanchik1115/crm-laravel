<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $service_id
 * @property int $quantity
 * @property float $unit_price
 */
class InvoiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'service_id' => $this->service_id,
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
        ];
    }
}
