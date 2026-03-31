<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

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
