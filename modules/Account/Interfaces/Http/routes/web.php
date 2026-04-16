<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Account\Interfaces\Http\Controllers\AccountController;
use Modules\Client\Domain\Entities\Client;

Route::group(['prefix' => 'accounts'], function () {
    Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/{id}', [AccountController::class, 'show'])->name('accounts.show');
});

// Just for show bad variant (N+1)
Route::get('/clients-slow', function () {
    DB::enableQueryLog();

    $clients = Client::all(); // without with()

    foreach ($clients as $client) {
        $client->accounts->count(); // N+1 problem
    }

    $queries = DB::getQueryLog();
    $queriesCount = count($queries);

    return "Кількість запитів: " . $queriesCount . " (N+1 проблема)";
});


// The right one is already exist /clients
// Route::get('/clients', [ClientController::class, 'index']);

// test get client route
Route::get('/test-client', function () {
    return 'Кiлькiсть клiєнтiв: ' . Client::count();
});

// test get account by id route
Route::get('/test-account/{id}', function ($id) {
    return Account::with('client')->find($id);
});
