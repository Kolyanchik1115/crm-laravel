<?php

declare(strict_types=1);

namespace Modules\Auth\src\Infrastructure\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\src\Domain\Entities\User;

/**
 * @extends Factory<\Modules\Auth\src\Domain\Entities\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'delivery_address' => $this->faker->address(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // Admin state
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => 'admin@crm.com',
            'first_name' => 'Admin',
            'last_name' => 'User',
        ]);
    }

    // Regular user state
    public function regular(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => 'user@crm.com',
            'first_name' => 'Regular',
            'last_name' => 'User',
        ]);
    }
}
