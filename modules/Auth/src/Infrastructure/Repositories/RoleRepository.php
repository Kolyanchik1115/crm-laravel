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
        return Role::where('name', $slugValue)->first();
    }

    public function getDefaultUserRole(): ?Role
    {
        return Role::where('name', RoleName::USER->value)->first();
    }

    public function assignRoleToUser(int $userId, int $roleId): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->roles()->syncWithoutDetaching([$roleId]);
        }
    }
}
