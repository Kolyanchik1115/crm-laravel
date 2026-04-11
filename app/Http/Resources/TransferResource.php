<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $transaction_out_id
 * @property int $transaction_in_id
 * @property float $amount
 * @property float $commission
 * @property string $created_at
 */
class TransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        //TODO: change this later
        return [
            'transaction_out_id' => $this['transaction_out_id'],
            'transaction_in_id' => $this['transaction_in_id'],
            'amount' => $this['amount'],
            'commission' => $this['commission'],
            'created_at' => now()->toISOString(),
        ];
    }
}
