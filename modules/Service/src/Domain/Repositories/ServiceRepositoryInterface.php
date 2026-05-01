<?php

declare(strict_types=1);

namespace Modules\Service\src\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Service\src\Domain\Entities\Service;

interface ServiceRepositoryInterface
{
    public function exists(int $id): bool;

    public function findById(int $id): ?Service;

    public function getAll(): Collection;

    public function findOrFail(int $id): Service;
}
