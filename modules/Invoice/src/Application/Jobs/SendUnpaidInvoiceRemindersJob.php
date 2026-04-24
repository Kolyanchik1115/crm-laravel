<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Application\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Modules\Invoice\src\Domain\Entities\Invoice;

class SendUnpaidInvoiceRemindersJob implements ShouldQueue
{
    use Queueable;

    // added onQueue in construct due to my Laravel version
    public function __construct()
    {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        // invoices with status 'unpaid' або 'overdue'
        $unpaidInvoices = Invoice::whereIn('status', ['unpaid', 'overdue'])
            ->whereDate('created_at', '<=', now()->subDays(3))
            ->get();

        $count = $unpaidInvoices->count();

        Log::info('SendUnpaidInvoiceRemindersJob: Знайдено неоплачених рахунків', [
            'count' => $count,
        ]);
    }
}
