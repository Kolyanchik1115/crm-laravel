<?php

declare(strict_types=1);

namespace Modules\Invoice\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Invoice\Application\Jobs\LogInvoiceAuditJob;
use Modules\Invoice\Domain\Events\InvoiceCreated;

class LogInvoiceAuditListener implements ShouldQueue
{
    public function handle(InvoiceCreated $event): void
    {
        LogInvoiceAuditJob::dispatch($event->invoiceId)->onQueue('audit');
    }
}
