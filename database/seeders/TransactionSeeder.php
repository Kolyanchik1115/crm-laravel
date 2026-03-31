<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // success
        Transaction::factory()->completed()->count(15)->create();

        // deposit
        Transaction::factory()->deposit()->completed()->count(5)->create();

        // large
        Transaction::factory()->large()->completed()->count(3)->create();

    }
}
