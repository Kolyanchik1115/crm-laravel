<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function exists(int $id): bool
    {
        return Service::where('id', $id)->exists();
    }

    public function findById(int $id): ?Service
    {
        return Service::find($id);
    }
}
