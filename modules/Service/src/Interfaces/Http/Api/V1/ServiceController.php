<?php

declare(strict_types=1);

namespace Modules\Service\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Service\src\Application\Services\ServiceService;
use Modules\Service\src\Interfaces\Http\Resources\V1\ServiceResource;

class ServiceController extends Controller
{
    public function __construct(
        private ServiceService $serviceService
    ) {
    }

    public function index(): JsonResponse
    {
        $services = $this->serviceService->getAllServicesPaginated(15);

        return ServiceResource::collection($services)
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $service = $this->serviceService->getServiceById($id);

        return (new ServiceResource($service))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }
}
