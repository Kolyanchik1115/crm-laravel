<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Repositories\ClientRepository;
use Illuminate\Database\Eloquent\Collection;

class ClientService
{
    protected ClientRepository $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all clients
     */
    public function getAllClients(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get client by ID
     */
    public function getClientById(int $id): Client
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * Create new client
     */
    public function createClient(array $data): Client
    {
        // Set default values
        $data['balance'] = $data['balance'] ?? 0;
        $data['currency'] = $data['currency'] ?? config('crm.default_currency', 'UAH');
        $data['is_active'] = $data['is_active'] ?? true;

        return $this->repository->create($data);
    }
}
