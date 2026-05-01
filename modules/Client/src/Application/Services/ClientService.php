<?php

declare(strict_types=1);

namespace Modules\Client\src\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Client\src\Domain\Repositories\ClientRepositoryInterface;

class ClientService
{
    // Cache key
    private const string CACHE_KEY_CLIENTS = 'crm:clients:list';
    // TTL - 2 min
    private const int CACHE_TTL = 120;

    public function __construct(
        protected ClientRepositoryInterface $repository
    ) {}

    /**
     * Get all clients (with cache)
     */
    public function getAllClients(): Collection
    {
        $cachedIds = Cache::get(self::CACHE_KEY_CLIENTS);

        if (is_array($cachedIds) && !empty($cachedIds)) {
            return Client::whereIn('id', $cachedIds)->get();
        }

        $clients = $this->repository->getAll();
        // Save only id instead
        Cache::put(self::CACHE_KEY_CLIENTS, $clients->pluck('id')->toArray(), self::CACHE_TTL);

        return $clients;
    }

    /**
     * Get all clients with pagination
     */
    public function getAllClientsPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($perPage);
    }

    /**
     * Get client by ID
     */
    public function getClientById(int $id): Client
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * Get client accounts
     */
    public function getClientAccounts(int $clientId): Collection
    {
        return $this->repository->getClientAccounts($clientId);
    }

    /**
     * Create new client and invalidate cache
     */
    public function createClient(array $data): Client
    {
        // Set default values
        $data['balance'] = $data['balance'] ?? 0;
        $data['currency'] = $data['currency'] ?? config('crm.default_currency', 'UAH');
        $data['is_active'] = $data['is_active'] ?? true;

        $client = $this->repository->create($data);

        // invalidate data
        Cache::forget(self::CACHE_KEY_CLIENTS);

        return $client;
    }
}
