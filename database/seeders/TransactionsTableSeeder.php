<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionsTableSeeder extends Seeder
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
