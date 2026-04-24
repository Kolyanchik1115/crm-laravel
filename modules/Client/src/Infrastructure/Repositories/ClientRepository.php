<?php

declare(strict_types=1);

namespace Modules\Client\src\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Client\src\Domain\Entities\Client;

class ClientRepository
{
    /**
     * @return Collection<int, Client>
     */
    public function getAll(): Collection
    {
        return Client::with('accounts')
            ->orderBy('full_name')
            ->get();
    }

    /**
     * Find clients by ID with accounts
     */
    public function findOrFail(int $id): Client
    {
        return Client::with('accounts')->findOrFail($id);
    }

    /**
     * Create new clients
     */
    public function create(array $data): Client
    {
        return Client::create($data);
    }
}
