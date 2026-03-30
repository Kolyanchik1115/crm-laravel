<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Active services
        Service::factory()->count(8)->active()->create();

        // Inactive services
        Service::factory()->count(2)->inactive()->create();

    }
}
