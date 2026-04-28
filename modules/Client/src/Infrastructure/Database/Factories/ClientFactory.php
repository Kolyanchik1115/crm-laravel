<?php

declare(strict_types=1);

namespace Modules\Client\src\Infrastructure\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Modules\Client\src\Domain\Entities\Client;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password_hash' => Hash::make('password123'),
            'balance' => $this->faker->randomFloat(2, 0, 100000),
            'currency' => $this->faker->randomElement(['UAH', 'USD', 'EUR']), // 3 element e.g.
            'is_active' => $this->faker->boolean(80),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // State: active user
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    // State inactive user
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    // State: high balance
    public function highBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $this->faker->randomFloat(2, 50000, 500000),
        ]);
    }
}
