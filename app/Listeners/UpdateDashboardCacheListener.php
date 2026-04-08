<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TransferCompleted;
use App\Jobs\UpdateDashboardCacheJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateDashboardCacheListener implements ShouldQueue
{
    public function handle(TransferCompleted $event): void
    {
        UpdateDashboardCacheJob::dispatch()
            ->onQueue('low')
            ->delay(now()->addSeconds(30));
    }
}
