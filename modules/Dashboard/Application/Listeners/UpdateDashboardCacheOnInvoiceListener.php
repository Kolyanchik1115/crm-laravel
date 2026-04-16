<?php

declare(strict_types=1);

namespace Modules\Dashboard\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Dashboard\Application\Jobs\UpdateDashboardCacheJob;
use Modules\Invoice\Domain\Events\InvoiceCreated;

class UpdateDashboardCacheOnInvoiceListener implements ShouldQueue
{
    public function handle(InvoiceCreated $event): void
    {
        UpdateDashboardCacheJob::dispatch()
            ->onQueue('low')
            ->delay(now()->addSeconds(30));
    }
}
