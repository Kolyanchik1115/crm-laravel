<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Account\src\Interfaces\Http\Api\V1\AccountController;

Route::prefix('v1')->group(function () {
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{account}/transactions', [AccountController::class, 'transactions'])
        ->name('accounts.transactions');
});
