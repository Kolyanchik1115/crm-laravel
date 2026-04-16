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
        Modules\Client\Providers\ClientServiceProvider::class,
        Modules\Account\Providers\AccountServiceProvider::class,
        Modules\Transaction\Providers\TransactionServiceProvider::class,
        Modules\Dashboard\Providers\DashboardServiceProvider::class,
        Modules\Invoice\Providers\InvoiceServiceProvider::class,
        Modules\Service\Providers\ServiceServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
