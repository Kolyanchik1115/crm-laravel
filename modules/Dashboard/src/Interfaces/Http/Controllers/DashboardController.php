<?php

declare(strict_types=1);

namespace Modules\Dashboard\src\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Dashboard\src\Application\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {
    }

    public function index(): View
    {
        $data = $this->dashboardService->getDashboardData();

        return view('dashboard::dashboard', [
            // Client stats - используем имена из вьюхи
            'clientsCount' => $data['clients']['total'],
            'activeClientsCount' => $data['clients']['active'],
            'inactiveClientsCount' => $data['clients']['inactive'],
            'totalBalance' => $data['clients']['total_balance'],
            'totalAccountsBalance' => $data['clients']['total_accounts_balance'],

            // Top clients
            'topClients' => $data['clients']['top_clients'],

            // Transaction stats
            'transactionsCount' => $data['transactions']['total_count'],
            'totalTransactionsAmount' => $data['transactions']['total_amount'],
            'amountByType' => $data['transactions']['amount_by_type'],
            'statusStats' => $data['transactions']['status_stats'],
            'recentTransactions' => $data['transactions']['recent'],
        ]);
    }
}
