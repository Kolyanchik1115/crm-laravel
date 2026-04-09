<?php

use App\Jobs\DailyCrmReportJob;
use App\Jobs\SendUnpaidInvoiceRemindersJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily report at 9:00
Schedule::job(new DailyCrmReportJob())
    ->dailyAt('09:00');

// Unpaid invoice reminder at 18:00
Schedule::job(new SendUnpaidInvoiceRemindersJob())
    ->dailyAt('18:00');
