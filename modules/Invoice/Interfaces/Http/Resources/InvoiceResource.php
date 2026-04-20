<?php

declare(strict_types=1);

namespace Modules\Invoice\Interfaces\Http\Resources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Modules\Client\Domain\Entities\Client;
use Modules\Client\Interfaces\Http\Resources\ClientResource;
use Modules\Service\Domain\Entities\Service;

/**
 * @property int $id
 * @property string $invoice_number
 * @property float $total_amount
 * @property string $status
 * @property Carbon|null $issued_at
 * @property int $client_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Client $clients
 * @property-read Collection|Service[] $items
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'issued_at' => $this->issued_at?->toISOString(),
            'client_id' => $this->client_id,
            'clients' => new ClientResource($this->whenLoaded('clients')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
