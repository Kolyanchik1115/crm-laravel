<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $types = ['deposit', 'withdrawal', 'transfer', 'payment'];
        $statuses = ['completed', 'pending', 'failed', 'cancelled'];

        return [
            'account_id' => Account::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 10000),
            'type' => $this->faker->randomElement($types),
            'status' => $this->faker->randomElement($statuses),
            'description' => $this->faker->sentence(),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }

    // State: success transaction
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
        ]);
    }

    // State: deposit
    public function deposit(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'deposit',
            'amount' => $this->faker->randomFloat(2, 100, 50000),
        ]);
    }

    // State: large amount
    public function large(): static
    {
        return $this->state(fn(array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 5000, 100000),
        ]);
    }
}
