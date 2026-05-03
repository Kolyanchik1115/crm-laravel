<?php

declare(strict_types=1);

namespace Modules\Auth\src\Infrastructure\Repositories;

use Modules\Auth\src\Domain\Entities\User;
use Modules\Auth\src\Domain\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function findByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = User::query()
            ->where('email', $email)
            ->first();

        return $user;
    }

    public function findById(int $id): ?User
    {
        /** @var User|null $user */
        $user = User::query()
            ->with('roles')
            ->find($id);

        return $user;
    }

    public function update(int $id, array $data): User
    {
        /** @var User $user */
        $user = User::query()->findOrFail($id);

        $user->update($data);

        return $user;
    }

    public function delete(int $id): bool
    {
        return User::query()->whereKey($id)->delete() > 0;
    }
}
