<?php

declare(strict_types=1);

namespace Modules\Account\Interfaces\Http\Resources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Modules\Client\Domain\Entities\Client;
use Modules\Client\Interfaces\Http\Resources\ClientResource;
use Modules\Transaction\Domain\Entities\Transaction;
use Modules\Transaction\Interfaces\Http\Resources\TransactionResource;

/**
 * @property int $id
 * @property string $account_number
 * @property float $balance
 * @property string $currency
 * @property int $client_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Client $clients
 * @property-read Collection|Transaction[] $transactions
 */
class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_number' => $this->account_number,
            'balance' => $this->balance,
            'currency' => $this->currency,
            'client_id' => $this->client_id,
            'clients' => new ClientResource($this->whenLoaded('clients')),
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
