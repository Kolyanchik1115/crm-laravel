<?php

declare(strict_types=1);

namespace Modules\Client\src\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Client\src\Domain\Entities\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::factory()->count(10)->create();

        // Special clients
        Client::factory()->highBalance()->count(3)->create();
        Client::factory()->inactive()->count(2)->create();
    }
}
