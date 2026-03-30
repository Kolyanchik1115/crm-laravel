<?php

namespace Database\Seeders;

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountsTableSeeder extends Seeder
{
    public function run(): void
    {
        // avg accounts
        Account::factory()->count(10)->create();

        // high balance
        Account::factory()->highBalance()->count(3)->create();

        // USD acc
        Account::factory()->usd()->count(3)->create();
    }
}
