<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Client;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
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
}
