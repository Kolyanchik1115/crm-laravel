<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            ClientsTableSeeder::class,
            AccountsTableSeeder::class,
            ServicesTableSeeder::class,
            InvoicesTableSeeder::class,
            InvoiceItemsTableSeeder::class,
            TransactionsTableSeeder::class,
        ]);
    }
}
