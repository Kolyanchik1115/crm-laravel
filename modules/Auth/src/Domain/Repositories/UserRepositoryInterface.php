<?php

declare(strict_types=1);

namespace Modules\Auth\src\Domain\Repositories;

use Modules\Auth\src\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
    public function update(int $id, array $data): User;
    public function delete(int $id): bool;
}
