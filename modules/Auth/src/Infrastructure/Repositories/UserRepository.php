<?php

declare(strict_types=1);

namespace Modules\Auth\src\Infrastructure\Repositories;

use Modules\Auth\src\Domain\Entities\User;
use Modules\Auth\src\Domain\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::with('roles')->find($id);
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete(int $id): bool
    {
        return User::destroy($id) > 0;
    }
}
