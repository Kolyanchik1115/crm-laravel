<?php

declare(strict_types=1);

namespace Modules\Client\Application\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Modules\Client\Domain\Entities\Client;
use Modules\Invoice\Domain\Entities\Invoice;
use Modules\Transaction\Domain\Entities\Transaction;

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
        $yesterday = now()->subDay()->toDateString();

        $transactionsCount = Transaction::whereDate('created_at', $yesterday)->count();
        $transactionsSum = Transaction::whereDate('created_at', $yesterday)->sum('amount');
        $newInvoicesCount = Invoice::whereDate('created_at', $yesterday)->count();
        $newClientsCount = Client::whereDate('created_at', $yesterday)->count();

        Log::info('DailyCrmReportJob: Звіт за ' . $yesterday, [
            'transactions_count' => $transactionsCount,
            'transactions_sum' => $transactionsSum,
            'new_invoices_count' => $newInvoicesCount,
            'new_clients_count' => $newClientsCount,
        ]);
    }
}
