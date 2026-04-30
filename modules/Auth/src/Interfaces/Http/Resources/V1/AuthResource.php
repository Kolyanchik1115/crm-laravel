<?php

declare(strict_types=1);

namespace Modules\Auth\src\Interfaces\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this['access_token'],
            'token_type' => 'bearer',
            'expires_in' => $this['expires_in'],
            //if needed
            'user' => new UserResource($this['user']),
        ];
    }
}
