<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Account\src\Providers\AccountServiceProvider;
use Modules\Auth\src\Domain\Exceptions\ForbiddenException;
use Modules\Auth\src\Domain\Exceptions\InsufficientRoleException;
use Modules\Auth\src\Infrastructure\Middleware\RoleMiddleware;
use Modules\Auth\src\Infrastructure\Middleware\WebRoleMiddleware;
use Modules\Auth\src\Providers\AuthServiceProvider;
use Modules\Client\src\Providers\ClientServiceProvider;
use Modules\Dashboard\src\Providers\DashboardServiceProvider;
use Modules\Invoice\src\Providers\InvoiceServiceProvider;
use Modules\Service\src\Providers\ServiceServiceProvider;
use Modules\Transaction\src\Domain\Exceptions\InsufficientBalanceException;
use Modules\Transaction\src\Domain\Exceptions\SameAccountTransferException;
use Modules\Transaction\src\Providers\EventServiceProvider as TransactionEventServiceProvider;
use Modules\Dashboard\src\Providers\EventServiceProvider as DashboardEventServiceProvider;
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
        //auth
        AuthServiceProvider::class,
        //client
        ClientServiceProvider::class,
        //account
        AccountServiceProvider::class,
        //transaction
        TransactionServiceProvider::class,
        TransactionEventServiceProvider::class,
        //dashboard
        DashboardServiceProvider::class,
        DashboardEventServiceProvider::class,
        //invoice
        InvoiceServiceProvider::class,
        //service
        ServiceServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware
        $middleware->append([
            HandleCors::class,
        ]);

        // API middleware group
        $middleware->group('api', [
            SubstituteBindings::class,
        ]);

        // Web middleware group
        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
        ]);

        // Auth middleware aliases
        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'role' => RoleMiddleware::class,
            'web.role' => WebRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            return redirect()->route('login');
        });

        // Forbidden (403)
        $exceptions->render(function (ForbiddenException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 403);
            }
            abort(403, $e->getMessage());
        });

        //  Insufficient Role (403)
        $exceptions->render(function (InsufficientRoleException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'required_roles' => $e->getRequiredRoles(),
                    'user_roles' => $e->getUserRoles(),
                ], 403);
            }
            abort(403, $e->getMessage());
        });

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
