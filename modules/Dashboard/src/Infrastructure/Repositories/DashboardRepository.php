<?php

declare(strict_types=1);

namespace Modules\Dashboard\src\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Transaction\src\Domain\Entities\Transaction;
use Modules\Dashboard\src\Domain\Repositories\DashboardRepositoryInterface;

class DashboardRepository implements DashboardRepositoryInterface
{
    /**
     * Get client statistics (aggregate)
     */
    public function getClientStats(): array
    {
        return [
            'total' => Client::count(),
            'active' => Client::where('is_active', true)->count(),
            'inactive' => Client::where('is_active', false)->count(),
            'total_balance' => Client::sum('balance'),
            'top_clients' => Client::orderBy('balance', 'desc')->limit(5)->get(['id', 'full_name', 'balance', 'currency']),
        ];
    }

    /**
     * Get total accounts balance (aggregate)
     */
    public function getTotalAccountsBalance(): float
    {
        return (float) DB::table('accounts')->sum('balance');
    }

    /**
     * Get transaction statistics (aggregate)
     */
    public function getTransactionStats(): array
    {
        return [
            'total_count' => Transaction::count(),
            'total_amount' => Transaction::sum('amount'),
            'amount_by_type' => Transaction::select(
                'type',
                DB::raw('count(*) as count'),
                DB::raw('sum(amount) as total')
            )
                ->groupBy('type')
                ->get()
                ->keyBy('type')
                ->map(fn ($item) => ['count' => $item->count, 'total' => $item->total]),
            'status_stats' => Transaction::select(
                'status',
                DB::raw('count(*) as count'),
                DB::raw('sum(amount) as total')
            )
                ->groupBy('status')
                ->get()
                ->keyBy('status')
                ->map(fn ($item) => ['count' => $item->count, 'total' => $item->total]),
            'recent' => Transaction::orderBy('created_at', 'desc')->limit(10)->get(),
        ];
    }
}
