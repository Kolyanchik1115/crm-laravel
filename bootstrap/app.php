<?php

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SameAccountTransferException;
use App\Http\Middleware\AddCorrelationId;
use App\Http\Middleware\SentryContextMiddleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api([
            AddCorrelationId::class,
        ]);
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
                Log::warning('Model not found', [
                    'model' => $e->getModel(),
                    'ids' => $e->getIds(),
                    'correlation_id' => $request->attributes->get('correlation_id'),
                ]);
                return response()->json([
                    'message' => 'Ресурс не знайдено.',
                ], 404);
            }
        });

        // Http exceptions (404, 403, etc)
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                Log::warning('Http exception', [
                    'status_code' => $e->getStatusCode(),
                    'message' => $e->getMessage(),
                    'correlation_id' => $request->attributes->get('correlation_id'),
                ]);
                return response()->json([
                    'message' => $e->getMessage() ?: 'Помилка запиту.',
                ], $e->getStatusCode());
            }
        });

        // Все інше (500) - неочікувані помилки
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                Log::error('Unexpected server error', [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'correlation_id' => $request->attributes->get('correlation_id'),
                ]);
                return response()->json([
                    'message' => 'Внутрішня помилка сервера.',
                ], 500);
            }
        });
    })
    ->create();
