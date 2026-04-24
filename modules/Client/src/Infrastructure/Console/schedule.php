<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;
use Modules\Client\src\Application\Jobs\DailyCrmReportJob;

// Daily report at 9:00
Schedule::job(new DailyCrmReportJob())
    ->dailyAt('09:00')
    ->name('daily_crm_report');
