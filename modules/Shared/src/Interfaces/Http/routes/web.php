<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

//TODO: Remove this route after creating the start page
Route::get('/', function () {
    return redirect()->route('login');
});

//Swagger
Route::middleware(['auth', 'web.role:ADMIN'])->group(function () {
    Route::get('/api-docs/openapi.yaml', function () {
        $yamlContent = file_get_contents(storage_path('api-docs/openapi.yaml'));
        return response($yamlContent, 200, [
            'Content-Type' => 'application/x-yaml',
        ]);
    });
});

//health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'environment' => app()->environment(),
    ]);
});
