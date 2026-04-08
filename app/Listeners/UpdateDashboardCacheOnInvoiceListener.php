<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Jobs\UpdateDashboardCacheJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateDashboardCacheOnInvoiceListener implements ShouldQueue
{
    public function handle(InvoiceCreated $event): void
    {
        UpdateDashboardCacheJob::dispatch()
            ->onQueue('low')
            ->delay(now()->addSeconds(30));
    }
}
