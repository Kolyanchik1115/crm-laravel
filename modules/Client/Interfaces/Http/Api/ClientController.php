<?php

declare(strict_types=1);

namespace Modules\Client\Interfaces\Http\Api;

use App\Http\Controllers\Controller;
use Modules\Client\Domain\Entities\Client;
use Illuminate\Http\JsonResponse;
use Modules\Client\Interfaces\Http\Resources\ClientResource;

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
