<?php

declare(strict_types=1);

namespace Modules\Client\src\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Client\src\Domain\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * Get all clients with accounts
     */
    public function getAll(): Collection
    {
        return Client::with('accounts')
            ->orderBy('full_name')
            ->get();
    }

    /**
     * Get all clients with pagination
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Client::with(['accounts', 'invoices'])
            ->orderBy('full_name')
            ->paginate($perPage);
    }

    /**
     * Find client by ID with accounts and invoices
     */
    public function findOrFail(int $id): Client
    {
        return Client::with(['accounts', 'invoices'])->findOrFail($id);
    }

    /**
     * Find client by ID (without relations)
     */
    public function findById(int $id): ?Client
    {
        return Client::find($id);
    }

    /**
     * Create new client
     */
    public function create(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * Get client accounts
     */
    public function getClientAccounts(int $clientId): Collection
    {
        $client = $this->findById($clientId);

        if (!$client) {
            return new Collection();
        }

        return $client->accounts;
    }
}
