<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

class ClientRepository
{
    /**
     * Get all clients with their accounts
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
