<?php

declare(strict_types=1);

namespace Modules\Service\src\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Service\src\Domain\Entities\Service;
use Modules\Service\src\Domain\Repositories\ServiceRepositoryInterface;

class ServiceService
{
    public function __construct(
        protected ServiceRepositoryInterface $serviceRepository
    ) {
    }

    public function getAllServices(): Collection
    {
        return $this->serviceRepository->getAll();
    }

    public function getServiceById(int $id): Service
    {
        return $this->serviceRepository->findOrFail($id);
    }

    public function serviceExists(int $id): bool
    {
        return $this->serviceRepository->exists($id);
    }

    public function getAllServicesPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->serviceRepository->getAllPaginated($perPage);
    }
}
