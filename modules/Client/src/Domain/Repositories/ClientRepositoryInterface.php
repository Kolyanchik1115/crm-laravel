<?php

declare(strict_types=1);

namespace Modules\Client\src\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Client\src\Domain\Entities\Client;

interface ClientRepositoryInterface
{
    /**
     * Get all clients with relations
     */
    public function getAll(): Collection;

    /**
     * Get all clients with pagination
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find client by ID with relations
     */
    public function findOrFail(int $id): Client;

    /**
     * Find client by ID (for check)
     */
    public function findById(int $id): ?Client;

    /**
     * Create new client
     */
    public function create(array $data): Client;

    /**
     * Get client accounts
     */
    public function getClientAccounts(int $clientId): Collection;
}
