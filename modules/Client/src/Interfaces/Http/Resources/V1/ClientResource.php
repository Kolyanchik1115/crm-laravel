<?php

declare(strict_types=1);

namespace Modules\Client\src\Interfaces\Http\Resources\V1;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Account\src\Interfaces\Http\Resources\V1\AccountResource;
use Modules\Invoice\Interfaces\Http\Resources\V1\InvoiceResource;
use Modules\Invoice\src\Domain\Entities\Invoice;

/**
 * @property int $id
 * @property string $full_name
 * @property string $email
 * @property float $balance
 * @property string $currency
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Account[] $accounts
 * @property-read Collection|Invoice[] $invoices
 */
class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'balance' => $this->balance,
            'currency' => $this->currency,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'accounts_count' => $this->whenCounted('accounts'),
            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),
        ];
    }
}
