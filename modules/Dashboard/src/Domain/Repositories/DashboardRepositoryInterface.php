<?php

declare(strict_types=1);

namespace Modules\Dashboard\src\Domain\Repositories;

interface DashboardRepositoryInterface
{
    public function getClientStats(): array;

    public function getTotalAccountsBalance(): float;

    public function getTransactionStats(): array;
}
