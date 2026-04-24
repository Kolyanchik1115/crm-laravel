<?php

declare(strict_types=1);

namespace Modules\Service\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Service\src\Domain\Entities\Service;
use Modules\Service\src\Interfaces\Http\Resources\V1\ServiceResource;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $services = Service::orderBy('name')->get();

        return ServiceResource::collection($services)
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $service = Service::findOrFail($id);

        return (new ServiceResource($service))
            ->response()
            ->setStatusCode(200);
    }
}
