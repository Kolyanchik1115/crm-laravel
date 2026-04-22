<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\InvoiceItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property float $total_amount
 * @property string $currency
 * @property string $status
 * @property Carbon|null $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection $items
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency ?? 'UAH',
            'status' => $this->status,
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
