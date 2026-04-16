<?php
declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Models\Account;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//TODO: Temporary route for CRM config (demo route)
Route::get('/crm-settings', function () {
    return [
        'default_currency' => config('crm.default_currency'),
        'min_transfer' => config('crm.min_transfer'),
        'max_transfer' => config('crm.max_transfer'),
    ];
});

//Transactions
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

//Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
