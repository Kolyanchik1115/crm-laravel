<?php

declare(strict_types=1);

namespace Modules\Service\Infrastructure\Repositories;

use Modules\Service\Domain\Entities\Service;
use Modules\Service\Domain\Repositories\ServiceRepositoryInterface;

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
