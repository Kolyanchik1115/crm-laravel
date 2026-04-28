<?php

declare(strict_types=1);

namespace Modules\Account\src\Infrastructure\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Client\src\Domain\Entities\Client;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'account_number' => $this->faker->unique()->numerify('UA##########'),
            'balance' => $this->faker->randomFloat(2, 0, 50000),
            'currency' => $this->faker->randomElement(['UAH', 'USD', 'EUR']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function highBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $this->faker->randomFloat(2, 50000, 200000),
        ]);
    }

    // State: currency in usd
    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'USD',
        ]);
    }
}
