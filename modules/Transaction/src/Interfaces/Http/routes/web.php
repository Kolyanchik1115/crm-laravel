<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Transaction\src\Interfaces\Http\Controllers\TransactionController;

Route::middleware(['auth', 'web.role:ADMIN,MANAGER,USER'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
});
