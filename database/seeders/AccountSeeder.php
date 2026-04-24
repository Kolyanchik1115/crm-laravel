<?php
declare(strict_types=1);

namespace Database\Seeders;

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Account\src\Domain\Entities\Account;

class AccountSeeder extends Seeder
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
