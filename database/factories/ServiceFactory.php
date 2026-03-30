<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $services = [
            'Консультація фінансового експерта',
            'Розробка корпоративного сайту',
            'SEO оптимізація',
            'Налаштування контекстної реклами',
            'SMM просування',
            'Бухгалтерське обслуговування',
            'Юридична консультація',
            'Розробка мобільного додатку',
            'Хостинг та підтримка',
            'CRM налаштування',
        ];

        return [
            'name' => $this->faker->randomElement($services),
            'description' => $this->faker->sentence(10),
            'base_price' => $this->faker->randomFloat(2, 500, 50000),
            'currency' => $this->faker->randomElement(['UAH', 'USD', 'EUR']),
            'is_active' => $this->faker->boolean(90),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // State: active service
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    // State: inactive service
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
