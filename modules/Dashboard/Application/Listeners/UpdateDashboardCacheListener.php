<?php

declare(strict_types=1);

namespace Modules\Dashboard\Application\Listeners;

use App\Events\TransferCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Dashboard\Application\Jobs\UpdateDashboardCacheJob;

class UpdateDashboardCacheListener implements ShouldQueue
{
    public function handle(TransferCompleted $event): void
    {
        UpdateDashboardCacheJob::dispatch()
            ->onQueue('low')
            ->delay(now()->addSeconds(30));
    }
}
