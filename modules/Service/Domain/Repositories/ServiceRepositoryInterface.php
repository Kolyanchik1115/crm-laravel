<?php

declare(strict_types=1);

namespace Modules\Service\Domain\Repositories;

use Modules\Service\Domain\Entities\Service;

interface ServiceRepositoryInterface
{
    public function exists(int $id): bool;

    public function findById(int $id): ?Service;
}
