<?php
declare(strict_types=1);

use Modules\Client\Application\Jobs\DailyCrmReportJob;

// Daily report at 9:00
Schedule::job(new DailyCrmReportJob())
    ->dailyAt('09:00')
    ->name('daily_crm_report');
