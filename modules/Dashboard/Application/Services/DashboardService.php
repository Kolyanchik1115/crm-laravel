<?php

declare(strict_types=1);

namespace Modules\Dashboard\Application\Services;

use Modules\Dashboard\Infrastructure\Repositories\DashboardRepository;

class DashboardService
{
    protected DashboardRepository $repository;

    public function __construct(DashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get clients statistics
     */
    public function getClientStats(): array
    {
        $clients = $this->repository->getAllClientsWithAccounts();

        return [
            'total' => $clients->count(),
            'active' => $clients->where('is_active', true)->count(),
            'inactive' => $clients->where('is_active', false)->count(),
            'total_balance' => $clients->sum('balance'),
            'total_accounts_balance' => $clients->flatMap(function ($client) {
                return $client->accounts;
            })->sum('balance'),
            'top_clients' => $clients->sortByDesc('balance')->take(5),
        ];
    }

    /**
     * Get transaction statistics
     */
    public function getTransactionStats(): array
    {
        $transactions = $this->repository->getAllTransactions();

        return [
            'total_count' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'amount_by_type' => $transactions->groupBy('type')->map(fn ($group) => [
                'total' => $group->sum('amount'),
                'count' => $group->count(),
            ]),
            'status_stats' => $transactions->groupBy('status')->map(fn ($group) => [
                'total' => $group->sum('amount'),
                'count' => $group->count(),
            ]),
            'recent' => $transactions
                ->where('created_at', '>=', now()->subDays(30))
                ->sortByDesc('created_at')
                ->take(10),
        ];
    }

    /**
     * Get all dashboard data
     */
    public function getDashboardData(): array
    {
        return [
            'clients' => $this->getClientStats(),
            'transactions' => $this->getTransactionStats(),
        ];
    }
}
