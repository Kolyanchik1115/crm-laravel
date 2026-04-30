<?php

declare(strict_types=1);

namespace Modules\Auth\src\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\src\Domain\Entities\Role;
use Modules\Auth\src\Domain\Entities\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::factory()->admin()->create();
        $userRole = Role::factory()->user()->create();

        $admin = User::factory()->admin()->create();
        $regular = User::factory()->regular()->create();

        $admin->roles()->attach($adminRole);
        $regular->roles()->attach($userRole);
    }
}
