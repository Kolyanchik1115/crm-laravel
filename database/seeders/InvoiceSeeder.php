<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Invoice\Domain\Entities\Invoice;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        // avg
        Invoice::factory()->count(10)->create();

        // paid
        Invoice::factory()->paid()->count(5)->create();

        // draft
        Invoice::factory()->draft()->count(3)->create();

        // overdue
        Invoice::factory()->overdue()->count(2)->create();

    }
}
