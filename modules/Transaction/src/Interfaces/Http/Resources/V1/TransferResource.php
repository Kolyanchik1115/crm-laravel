<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Interfaces\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use stdClass;

class TransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $resource = $this->resource;

        if (is_array($resource) || $resource instanceof stdClass) {
            $data = (array) $resource;

            return [
                'id' => $data['transaction_out_id'] ?? null,
                'account_from_id' => $data['account_from_id'] ?? null,
                'account_to_id' => $data['account_to_id'] ?? null,
                'amount' => (float) ($data['amount'] ?? 0),
                'currency' => $data['currency'] ?? 'UAH',
                'status' => $data['status'] ?? 'completed',
                'description' => $data['description'] ?? '',
                'commission' => (float) ($data['commission'] ?? 0),
                'created_at' => $data['created_at'] ?? now()->toIso8601String(),
            ];
        }

        return [
            'id' => $resource->id,
            'account_from_id' => $resource->account_from_id ?? $resource->account_id,
            'account_to_id' => $resource->account_to_id ?? null,
            'amount' => (float) $resource->amount,
            'currency' => $resource->currency ?? 'UAH',
            'status' => $resource->status ?? 'completed',
            'description' => $resource->description ?? '',
            'commission' => (float) ($resource->commission ?? 0),
            'created_at' => $resource->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }
}
