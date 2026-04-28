<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Infrastructure\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Invoice\src\Domain\Entities\Invoice;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];

        return [
            'client_id' => Client::factory(),
            'invoice_number' => $this->faker->unique()->numerify('INV-####-####'),
            'total_amount' => $this->faker->randomFloat(2, 1000, 100000),
            'status' => $this->faker->randomElement($statuses),
            'issued_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // State: paid
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    // State: draft
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    // State: overdue
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'issued_at' => $this->faker->dateTimeBetween('-2 years', '-1 month'),
        ]);
    }
}
