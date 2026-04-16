<?php

declare(strict_types=1);

namespace Modules\Dashboard\Application\Listeners;

use App\Events\InvoiceCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Dashboard\Application\Jobs\UpdateDashboardCacheJob;

class UpdateDashboardCacheOnInvoiceListener implements ShouldQueue
{
    public function handle(InvoiceCreated $event): void
    {
        UpdateDashboardCacheJob::dispatch()
            ->onQueue('low')
            ->delay(now()->addSeconds(30));
    }
}
