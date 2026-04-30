<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Auth\src\Interfaces\Http\Api\V1\AuthController;

Route::prefix('v1/auth')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Protected routes
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});
