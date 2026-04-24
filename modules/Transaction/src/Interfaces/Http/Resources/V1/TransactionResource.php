<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Interfaces\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Account\src\Interfaces\Http\Resources\V1\AccountResource;

/**
 * @property int $id
 * @property float $amount
 * @property string $type
 * @property string $status
 * @property string|null $description
 * @property int $account_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Account $accounts
 */
class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'account_id' => $this->account_id,
            'accounts' => new AccountResource($this->whenLoaded('accounts')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
