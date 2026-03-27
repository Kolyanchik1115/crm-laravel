<?php

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
