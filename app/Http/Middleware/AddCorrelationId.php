<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

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

        Log::withContext(['correlation_id' => $correlationId]);

        $response = $next($request);

        $response->headers->set('X-Correlation-Id', $correlationId);

        return $response;
    }
}
