<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Account\Domain\Entities\Account;
use Modules\Account\Interfaces\Http\Controllers\AccountController;

// Accounts
Route::group(['prefix' => 'accounts'], function () {
    Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/{id}', [AccountController::class, 'show'])->name('accounts.show');
});

// Test route
Route::get('/test-accounts/{id}', function ($id) {
    return Account::with('client')->find($id);
});
