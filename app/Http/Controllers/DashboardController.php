<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): View
    {
        $data = $this->dashboardService->getDashboardData();

        return view('dashboard', [
            // Client stats
            'clientsCount' => $data['clients']['total'],
            'activeClientsCount' => $data['clients']['active'],
            'inactiveClientsCount' => $data['clients']['inactive'],
            'totalBalance' => $data['clients']['total_balance'],
            'totalAccountsBalance' => $data['clients']['total_accounts_balance'],
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
