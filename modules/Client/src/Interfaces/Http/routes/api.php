<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Client\src\Interfaces\Http\Api\V1\ClientController;

Route::prefix('v1')->group(function () {
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{id}/accounts', [ClientController::class, 'accounts'])->name('clients.accounts');
});
