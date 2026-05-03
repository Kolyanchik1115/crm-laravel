<?php

declare(strict_types=1);

namespace Modules\Shared\src\Infrastructure\Middleware;

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
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->header('X-Correlation-Id');
        if (empty($correlationId)) {
            $correlationId = Str::uuid()->toString();
        }

        $request->attributes->set('correlation_id', $correlationId);
        $endpoint = $request->method() . ' ' . $request->path();
        $module = $this->getModuleFromPath($request->path());

        if ($correlationId && class_exists(Integration::class)) {
            configureScope(function (Scope $scope) use ($correlationId, $endpoint, $module): void {
                $scope->setTag('correlation_id', $correlationId);
                $scope->setTag('endpoint', $endpoint);
                $scope->setTag('module', $module);
                $scope->setExtra('correlation_id', $correlationId);

                if (Auth::check()) {
                    $scope->setUser(['id' => (string)Auth::id()]);
                }
            });
        }

        Log::withContext([
            'correlation_id' => $correlationId,
            'endpoint' => $endpoint,
            'module' => $module,
        ]);

        $response = $next($request);

        $response->headers->set('X-Correlation-Id', $correlationId);

        return $response;
    }

    private function getModuleFromPath(string $path): string
    {
        return match (true) {
            str_contains($path, 'transfers') => 'transfers',
            str_contains($path, 'invoices') => 'invoices',
            str_contains($path, 'accounts') => 'accounts',
            str_contains($path, 'clients') => 'clients',
            str_contains($path, 'services') => 'services',
            str_contains($path, 'dashboard') => 'dashboard',
            default => 'api',
        };
    }
}
