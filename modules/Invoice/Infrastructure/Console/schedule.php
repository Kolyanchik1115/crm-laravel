<?php
declare(strict_types=1);

use Modules\Invoice\Application\Jobs\SendUnpaidInvoiceRemindersJob;

// Unpaid invoice reminder at 18:00
Schedule::job(new SendUnpaidInvoiceRemindersJob())
    ->dailyAt('18:00')
    ->name('unpaid_invoice_reminder');
