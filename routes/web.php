<?php

use App\Http\Controllers\ClientController;
use App\Models\Client;
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

// CRM routes
Route::get('/clients', function () {
    return 'Сторінка клієнтів (буде реалізовано)';
});

Route::get('/accounts', function () {
    return 'Сторінка рахунків (буде реалізовано)';
});

Route::get('/transactions', function () {
    return 'Сторінка транзакцій (буде реалізовано)';
});

Route::get('/invoices', function () {
    return 'Сторінка рахунків-фактур (буде реалізовано)';
});

// test get client route
Route::get('/test-client', function () {
    return 'Кiлькiсть клiєнтiв: ' . Client::count();
});

// test get account by id route
Route::get('/test-account/{id}', function ($id) {
    return Account::with('client')->find($id);
});

// Clients
Route::group(['prefix' => 'clients'], function () {
    Route::get('/', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/{id}', [ClientController::class, 'show'])->name('clients.show');
});
