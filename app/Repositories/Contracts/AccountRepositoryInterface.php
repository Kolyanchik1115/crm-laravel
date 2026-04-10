<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Account;

interface AccountRepositoryInterface
{
    public function findById(int $id): ?Account;

    public function decrementBalance(int $id, string $amount): void;

    public function incrementBalance(int $id, string $amount): void;
}
