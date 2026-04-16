<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Service\Domain\Entities\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Active services
        Service::factory()->count(8)->active()->create();

        // Inactive services
        Service::factory()->count(2)->inactive()->create();

    }
}
