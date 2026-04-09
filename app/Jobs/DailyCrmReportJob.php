<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DailyCrmReportJob implements ShouldQueue
{
    use Queueable;

    // added onQueue in construct due to my Laravel version
    public function __construct()
    {
        $this->onQueue('reports');
    }

    public function handle(): void
    {
        $yesterday = now()->subDay();

        $transactionsCount = Transaction::whereDate('created_at', $yesterday)->count();
        $transactionsSum = Transaction::whereDate('created_at', $yesterday)->sum('amount');
        $newInvoicesCount = Invoice::whereDate('created_at', $yesterday)->count();
        $newClientsCount = Client::whereDate('created_at', $yesterday)->count();

        Log::info('DailyCrmReportJob: Звіт за ' . $yesterday->toDateString(), [
            'transactions_count' => $transactionsCount,
            'transactions_sum' => $transactionsSum,
            'new_invoices_count' => $newInvoicesCount,
            'new_clients_count' => $newClientsCount,
        ]);
    }
}
