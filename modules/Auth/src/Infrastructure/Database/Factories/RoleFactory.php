<?php

declare(strict_types=1);

namespace Modules\Auth\src\Infrastructure\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\src\Domain\Entities\Role;
use Modules\Auth\src\Domain\Enums\RoleName;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([RoleName::USER, RoleName::ADMIN]),
        ];
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleName::USER,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleName::ADMIN,
        ]);
    }
}
