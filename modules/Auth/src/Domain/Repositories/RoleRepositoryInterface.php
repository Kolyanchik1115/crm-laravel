<?php

declare(strict_types=1);

namespace Modules\Auth\src\Domain\Repositories;

use Modules\Auth\src\Domain\Entities\Role;
use Modules\Auth\src\Domain\Enums\RoleName;

interface RoleRepositoryInterface
{
    public function findBySlug(RoleName|string $slug): ?Role;
    public function getDefaultUserRole(): ?Role;
    public function assignRoleToUser(int $userId, int $roleId): void;
}
