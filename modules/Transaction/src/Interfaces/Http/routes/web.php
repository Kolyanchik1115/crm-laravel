<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Transaction\src\Interfaces\Http\Controllers\TransactionController;

Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
