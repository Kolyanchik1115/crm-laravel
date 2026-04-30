<?php

declare(strict_types=1);

namespace Modules\Auth\src\Application\DTO;

final readonly class RegisterDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            password: $data['password'],
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'is_active' => true,
        ];
    }
}
