<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Account\src\Infrastructure\Database\Seeders\AccountSeeder;
use Modules\Client\src\Infrastructure\Database\Seeders\ClientSeeder;
use Modules\Invoice\src\Infrastructure\Database\Seeders\InvoiceItemSeeder;
use Modules\Invoice\src\Infrastructure\Database\Seeders\InvoiceSeeder;
use Modules\Service\src\Infrastructure\Database\Seeders\ServiceSeeder;
use Modules\Transaction\src\Infrastructure\Database\Seeders\TransactionSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //TODO: when you need this user table, uncommit below

        // User::factory(10)->create();
        // User::factory()->create([
        // 'name' => 'Test User',
        // 'email' => 'test@example.com', ]);

        $this->call([
            ClientSeeder::class,
            AccountSeeder::class,
            ServiceSeeder::class,
            InvoiceSeeder::class,
            InvoiceItemSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
