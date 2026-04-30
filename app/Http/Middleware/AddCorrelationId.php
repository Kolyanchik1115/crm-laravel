<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Sentry\Laravel\Integration;
use Sentry\State\Scope;
use Symfony\Component\HttpFoundation\Response;

use function Sentry\configureScope;

class AddCorrelationId
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->header('X-Correlation-Id');
        if (empty($correlationId)) {
            $correlationId = Str::uuid()->toString();
        }

        $request->attributes->set('correlation_id', $correlationId);

        if ($correlationId && class_exists(Integration::class)) {
            configureScope(function (Scope $scope) use ($request, $correlationId): void {
                $scope->setTag('correlation_id', $correlationId);
                $scope->setTag('endpoint', $request->method() . ' ' . $request->path());
                $scope->setExtra('correlation_id', $correlationId);

                if (Auth::check()) {
                    $scope->setUser(['id' => (string)Auth::id()]);
                }
            });
        }

        Log::withContext(['correlation_id' => $correlationId]);

        $response = $next($request);

        $response->headers->set('X-Correlation-Id', $correlationId);

        return $response;
    }
}
