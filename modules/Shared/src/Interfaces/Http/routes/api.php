<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'environment' => app()->environment(),
    ]);
});

//Test sentry controller
if (app()->environment() !== 'production') {
    Route::get('/test-sentry', function () {
        throw new \RuntimeException('Test Sentry integration - ' . now()->toDateTimeString());
    });
}
