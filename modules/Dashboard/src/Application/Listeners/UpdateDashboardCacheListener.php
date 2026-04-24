<?php

declare(strict_types=1);

namespace Modules\Dashboard\src\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Dashboard\src\Application\Jobs\UpdateDashboardCacheJob;
use Modules\Transaction\src\Domain\Events\TransferCompleted;

class UpdateDashboardCacheListener implements ShouldQueue
{
    public function handle(TransferCompleted $event): void
    {
        UpdateDashboardCacheJob::dispatch()
            ->onQueue('low')
            ->delay(now()->addSeconds(30));
    }
}
