<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Account\src\Providers\AccountServiceProvider;
use Modules\Client\src\Providers\ClientServiceProvider;
use Modules\Dashboard\src\Providers\DashboardServiceProvider;
use Modules\Invoice\src\Providers\InvoiceServiceProvider;
use Modules\Service\src\Providers\ServiceServiceProvider;
use Modules\Transaction\src\Domain\Exceptions\InsufficientBalanceException;
use Modules\Transaction\src\Domain\Exceptions\SameAccountTransferException;
use Modules\Transaction\src\Providers\EventServiceProvider;
use Modules\Transaction\src\Providers\TransactionServiceProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        //client
        ClientServiceProvider::class,
        //account
        AccountServiceProvider::class,
        //transaction
        TransactionServiceProvider::class,
        EventServiceProvider::class,
        //dashboard
        DashboardServiceProvider::class,
        EventServiceProvider::class,
        //invoice
        InvoiceServiceProvider::class,
        //service
        ServiceServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Validation errors (422)
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // InsufficientBalanceException (422)
        $exceptions->render(function (InsufficientBalanceException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Недостатньо коштів на рахунку.',
                    'code' => 'INSUFFICIENT_BALANCE',
                ], 422);
            }
        });

        // SameAccountTransferException (422)
        $exceptions->render(function (SameAccountTransferException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Рахунок відправника і одержувача не можуть збігатися.',
                    'code' => 'SAME_ACCOUNT_TRANSFER',
                ], 422);
            }
        });

        // DomainException (422)
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'code' => 'DOMAIN_ERROR',
                ], 422);
            }
        });

        // Model not found (404)
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Ресурс не знайдено.',
                ], 404);
            }
        });

        // Http exceptions (404, 403, etc)
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Помилка запиту.',
                ], $e->getStatusCode());
            }
        });

        // Other (500)
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Внутрішня помилка сервера.',
                ], 500);
            }
        });
    })
    ->create();
