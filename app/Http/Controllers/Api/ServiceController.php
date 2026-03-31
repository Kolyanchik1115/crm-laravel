<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Service;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use Illuminate\Http\JsonResponse;

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
