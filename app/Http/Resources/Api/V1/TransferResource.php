<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $account_from_id
 * @property int $account_to_id
 * @property float $amount
 * @property string $currency
 * @property string $status
 * @property string $description
 * @property float $commission
 * @property \Illuminate\Support\Carbon $created_at
 */
class TransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_from_id' => $this->account_from_id,
            'account_to_id' => $this->account_to_id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency ?? 'UAH',
            'status' => $this->status ?? 'completed',
            'description' => $this->description ?? '',
            'commission' => (float) ($this->commission ?? 0),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
