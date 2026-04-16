<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Transaction\Interfaces\Http\Api\TransactionController;
use Modules\Transaction\Interfaces\Http\Api\TransferController;

Route::prefix('v1')->group(function () {
    //Transaction
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    //Transfer
    Route::post('/transfer', [TransferController::class, 'transfer']);
});
