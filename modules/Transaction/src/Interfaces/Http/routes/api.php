<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Transaction\src\Interfaces\Http\Api\V1\TransactionController;
use Modules\Transaction\src\Interfaces\Http\Api\V1\TransferController;

Route::prefix('v1')->middleware(['auth:api', 'role:ADMIN,MANAGER,USER'])->group(function () {
    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    // Transfers
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/{id}', [TransferController::class, 'show'])->name('transfers.show');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
});
