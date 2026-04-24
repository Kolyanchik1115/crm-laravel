<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Invoice\src\Application\Jobs\LogInvoiceAuditJob;
use Modules\Invoice\src\Domain\Events\InvoiceCreated;

class LogInvoiceAuditListener implements ShouldQueue
{
    public function handle(InvoiceCreated $event): void
    {
        LogInvoiceAuditJob::dispatch($event->invoiceId)->onQueue('audit');
    }
}
