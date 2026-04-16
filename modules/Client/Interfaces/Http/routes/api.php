<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Client\Interfaces\Http\Api\ClientController;

Route::prefix('v1')->group(function () {
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{id}', [ClientController::class, 'show']);
});
