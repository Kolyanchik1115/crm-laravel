<?php

declare(strict_types=1);

namespace Modules\Service\src\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Service\src\Domain\Entities\Service;
use Modules\Service\src\Domain\Repositories\ServiceRepositoryInterface;

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

    public function findOrFail(int $id): Service
    {
        return Service::findOrFail($id);
    }

    public function getAll(): Collection
    {
        return Service::orderBy('name')->get();
    }
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Service::orderBy('name')->paginate($perPage);
    }
}
