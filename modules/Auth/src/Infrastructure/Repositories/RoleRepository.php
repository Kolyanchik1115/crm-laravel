<?php

declare(strict_types=1);

namespace Modules\Auth\src\Infrastructure\Repositories;

use Modules\Auth\src\Domain\Entities\Role;
use Modules\Auth\src\Domain\Entities\User;
use Modules\Auth\src\Domain\Enums\RoleName;
use Modules\Auth\src\Domain\Repositories\RoleRepositoryInterface;

class RoleRepository implements RoleRepositoryInterface
{
    public function findBySlug(RoleName|string $slug): ?Role
    {
        $slugValue = $slug instanceof RoleName ? $slug->value : $slug;

        /** @var Role|null $role */
        $role = Role::where('name', $slugValue)->first();

        return $role;
    }

    public function getDefaultUserRole(): ?Role
    {
        /** @var Role|null $role */
        $role = Role::where('name', RoleName::USER->value)->first();

        return $role;
    }

    public function assignRoleToUser(int $userId, int $roleId): void
    {
        /** @var User|null $user */
        $user = User::find($userId);
        if ($user) {
            $user->roles()->syncWithoutDetaching([$roleId]);
        }
    }
}
