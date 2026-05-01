<?php

declare(strict_types=1);

namespace Modules\Client\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Client\src\Application\Services\ClientService;
use Modules\Client\src\Interfaces\Http\Resources\V1\ClientResource;
use Modules\Account\src\Interfaces\Http\Resources\V1\AccountResource;

class ClientController extends Controller
{
    public function __construct(
        private ClientService $clientService
    )
    {
    }

    /**
     * Display a listing of clients
     */
    public function index(): JsonResponse
    {
        $clients = $this->clientService
            ->getAllClientsPaginated(5);

        return ClientResource::collection($clients)
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Display the specified client
     */
    public function show(int $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);

        return (new ClientResource($client))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * GET /api/v1/clients/{id}/accounts
     */
    public function accounts(int $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);

        $accounts = $this->clientService->getClientAccounts($id);

        return AccountResource::collection($accounts)
            ->additional([
                'success' => true,
                'client_id' => $client->id,
                'client_name' => $client->full_name,
            ])
            ->response()
            ->setStatusCode(200);
    }
}
