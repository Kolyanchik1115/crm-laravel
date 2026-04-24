<?php

declare(strict_types=1);

namespace Modules\Account\src\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Account\src\Domain\Entities\Account;

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
