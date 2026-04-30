<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Account\src\Interfaces\Http\Controllers\AccountController;

// Accounts - ADMIN, MANAGER и USER
Route::middleware(['auth', 'web.role:ADMIN,MANAGER,USER'])->prefix('accounts')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/{id}', [AccountController::class, 'show'])->name('accounts.show');
});

// Test route - Admin only
Route::middleware(['auth', 'web.role:ADMIN'])->get('/test-accounts/{id}', function ($id) {
    return Account::with('client')->find($id);
});
