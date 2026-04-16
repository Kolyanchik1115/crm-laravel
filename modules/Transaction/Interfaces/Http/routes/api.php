<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Transaction\Interfaces\Http\Api\TransactionController;

Route::prefix('v1')->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
});
