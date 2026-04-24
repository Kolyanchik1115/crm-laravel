<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        //client
        Modules\Client\Providers\ClientServiceProvider::class,
        //account
        Modules\Account\Providers\AccountServiceProvider::class,
        //transaction
        Modules\Transaction\Providers\TransactionServiceProvider::class,
        Modules\Transaction\Providers\EventServiceProvider::class,
        //dashboard
        Modules\Dashboard\Providers\DashboardServiceProvider::class,
        Modules\Dashboard\Providers\EventServiceProvider::class,
        //invoice
        Modules\Invoice\Providers\InvoiceServiceProvider::class,
        //service
        Modules\Service\Providers\ServiceServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
