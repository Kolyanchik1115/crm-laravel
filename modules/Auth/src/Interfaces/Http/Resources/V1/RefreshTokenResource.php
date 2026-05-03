<?php

declare(strict_types=1);

namespace Modules\Auth\src\Interfaces\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefreshTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource['access_token'],
            'token_type' => $this->resource['token_type'],
            'expires_in' => $this->resource['expires_in'],
        ];
    }
}
