<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Repositories\ClientRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ClientService
{
    protected ClientRepository $repository;

    // Cache key
    private const string CACHE_KEY_CLIENTS = 'crm:clients:list';
    // TTL - 2 min
    private const int CACHE_TTL = 120;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all clients (with cache)
     */
    public function getAllClients(): Collection
    {
        $cached = Cache::get(self::CACHE_KEY_CLIENTS);

        // If there is a cache, and it is of the correct type
        if ($cached instanceof Collection) {
            return $cached;
        }

        // If there is no cache, or it is corrupted, we take it from the database
        $clients = $this->repository->getAll();

        // Save in cache
        Cache::put(self::CACHE_KEY_CLIENTS, $clients, self::CACHE_TTL);

        return $clients;
    }


    /**
     * Get client by ID
     */
    public function getClientById(int $id): Client
    {
        return $this->repository->findOrFail($id);
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
