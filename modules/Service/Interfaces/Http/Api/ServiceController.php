<?php

declare(strict_types=1);

namespace Modules\Service\Interfaces\Http\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Service\Domain\Entities\Service;
use Modules\Service\Interfaces\Http\Resources\ServiceResource;

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
