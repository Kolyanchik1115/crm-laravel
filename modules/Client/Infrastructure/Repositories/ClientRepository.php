<?php

declare(strict_types=1);

namespace Modules\Client\Infrastructure\Repositories;

use Modules\Client\Domain\Entities\Client;
use Illuminate\Database\Eloquent\Collection;

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
     * Find client by ID with accounts
     */
    public function findOrFail(int $id): Client
    {
        return Client::with('accounts')->findOrFail($id);
    }

    /**
     * Create new client
     */
    public function create(array $data): Client
    {
        return Client::create($data);
    }
}
