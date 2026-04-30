<?php

declare(strict_types=1);

namespace Modules\Auth\src\Application\DTO;

final readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
