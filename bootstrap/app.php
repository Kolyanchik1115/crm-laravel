<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
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
use Modules\Shared\src\Infrastructure\Middleware\AddCorrelationId;
use Modules\Shared\src\Providers\TelescopeServiceProvider;
use Modules\Transaction\src\Domain\Exceptions\InsufficientBalanceException;
use Modules\Transaction\src\Domain\Exceptions\SameAccountTransferException;
use Modules\Transaction\src\Providers\EventServiceProvider as TransactionEventServiceProvider;
use Modules\Dashboard\src\Providers\EventServiceProvider as DashboardEventServiceProvider;
use Modules\Invoice\src\Providers\EventServiceProvider as InvoiceEventServiceProvider;
use Modules\Transaction\src\Providers\TransactionServiceProvider;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        AuthServiceProvider::class,
        ClientServiceProvider::class,
        AccountServiceProvider::class,
        TransactionServiceProvider::class,
        TransactionEventServiceProvider::class,
        DashboardServiceProvider::class,
        DashboardEventServiceProvider::class,
        InvoiceServiceProvider::class,
        InvoiceEventServiceProvider::class,
        ServiceServiceProvider::class,
        TelescopeServiceProvider::class
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([HandleCors::class]);

        $middleware->group('api', [
            SubstituteBindings::class,
            AddCorrelationId::class,
        ]);

        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'role' => RoleMiddleware::class,
            'web.role' => WebRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Sentry
        Integration::handles($exceptions);

        // ===== 401 Unauthenticated =====
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please log in.',
                    'code' => 'UNAUTHENTICATED',
                ], 401);
            }
            return redirect()->route('login');
        });

        // ===== 403 Forbidden =====
        $exceptions->render(function (ForbiddenException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 'FORBIDDEN',
                ], 403);
            }
            abort(403, $e->getMessage());
        });

        // ===== 403 Insufficient Role =====
        $exceptions->render(function (InsufficientRoleException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 'INSUFFICIENT_ROLE',
                    'required_roles' => $e->getRequiredRoles(),
                    'user_roles' => $e->getUserRoles(),
                ], 403);
            }
            abort(403, $e->getMessage());
        });

        // ===== 404 Not Found =====
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ресурс не знайдено',
                    'code' => 'RESOURCE_NOT_FOUND',
                ], 404);
            }
            abort(404);
        });

        // ===== 422 Validation Error =====
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Помилка валідації',
                    'code' => 'VALIDATION_ERROR',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // ===== 422 Insufficient Balance =====
        $exceptions->render(function (InsufficientBalanceException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 'INSUFFICIENT_BALANCE',
                ], 422);
            }
            abort(422, $e->getMessage());
        });

        // ===== 422 Same Account Transfer =====
        $exceptions->render(function (SameAccountTransferException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 'SAME_ACCOUNT_TRANSFER',
                ], 422);
            }
            abort(422, $e->getMessage());
        });

        // ===== 422 Domain Exception =====
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 'DOMAIN_ERROR',
                ], 422);
            }
            abort(422, $e->getMessage());
        });

        // ===== Other Http Exceptions (4xx, 5xx) =====
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Помилка запиту',
                    'code' => 'HTTP_ERROR',
                ], $e->getStatusCode());
            }
        });

        // ===== 500 Internal Server Error =====
        $exceptions->render(function (Throwable $e, Request $request) {
            // Логируем ошибку
            \Log::error('Internal Server Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (($request->expectsJson() || $request->is('api/*')) && !config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Внутрішня помилка сервера',
                    'code' => 'INTERNAL_SERVER_ERROR',
                ], 500);
            }
        });
    })
    ->create();
