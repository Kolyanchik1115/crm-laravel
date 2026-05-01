<?php

declare(strict_types=1);

namespace Modules\Dashboard\src\Application\Services;

use Modules\Dashboard\src\Domain\Repositories\DashboardRepositoryInterface;

class DashboardService
{
    public function __construct(
        protected DashboardRepositoryInterface $repository
    ) {
    }

    public function getDashboardData(): array
    {
        $clientStats = $this->repository->getClientStats();

        return [
            'clients' => array_merge($clientStats, [
                'total_accounts_balance' => $this->repository->getTotalAccountsBalance(),
            ]),
            'transactions' => $this->repository->getTransactionStats(),
        ];
    }
}
