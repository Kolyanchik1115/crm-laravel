<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

interface AccountRepositoryInterface
{
    /**
     * Get all accounts with their clients
     *
     * @return Collection<int, Account>
     */
    public function getAll(): Collection;

    public function findById(int $id): ?Account;

    public function decrementBalance(int $id, string $amount): void;

    public function incrementBalance(int $id, string $amount): void;
}
