<?php

declare(strict_types=1);

namespace Modules\Account\src\Interfaces\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $account_number
 * @property float $balance
 * @property string $currency
 * @property int $client_id
 * @property Carbon|null $created_at
 */
class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'account_number' => $this->account_number,
            'balance' => (float) $this->balance,
            'currency' => $this->currency,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
