<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Jobs\LogInvoiceAuditJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogInvoiceAuditListener implements ShouldQueue
{
    public function handle(InvoiceCreated $event): void
    {
        LogInvoiceAuditJob::dispatch($event->invoiceId)->onQueue('audit');
    }
}
