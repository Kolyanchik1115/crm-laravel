<?php

declare(strict_types=1);

namespace Modules\Service\src\Infrastructure\Repositories;

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
}
