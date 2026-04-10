<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Service;

interface ServiceRepositoryInterface
{
    public function exists(int $id): bool;

    public function findById(int $id): ?Service;
}
