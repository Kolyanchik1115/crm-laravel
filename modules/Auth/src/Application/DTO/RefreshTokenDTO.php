<?php

declare(strict_types=1);

namespace Modules\Auth\src\Application\DTO;

final readonly class RefreshTokenDTO
{
    public function __construct(
        public string $access_token,
        public string $token_type,
        public int    $expires_in,
    ) {
    }

    public static function fromToken(string $token): self
    {
        return new self(
            access_token: $token,
            token_type: 'bearer',
            expires_in: config('jwt.ttl', 60) * 60,
        );
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->access_token,
            'token_type' => $this->token_type,
            'expires_in' => $this->expires_in,
        ];
    }
}
