<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AccountResource;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        $clients = Client::with(['accounts', 'invoices'])
            ->orderBy('full_name')
            ->get();

        return ClientResource::collection($clients)
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $client = Client::with(['accounts', 'invoices'])->findOrFail($id);

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * GET /api/v1/clients/{id}/accounts
     */
    public function accounts(int $id): JsonResponse
    {
        $client = Client::with('accounts')->find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        return AccountResource::collection($client->accounts)
            ->additional([
                'client_id' => $client->id,
                'client_name' => $client->full_name,
            ])
            ->response()
            ->setStatusCode(200);
    }
}
