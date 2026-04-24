<?php

declare(strict_types=1);

namespace Modules\Client\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Client\src\Interfaces\Http\Resources\V1\ClientResource;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        $clients = Client::with(['accounts', 'invoices'])
            ->orderBy('full_name')
            ->paginate(15);

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
