<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Account\Domain\Entities\Account;
use Modules\Client\Domain\Entities\Client;

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

Route::get('/test-client', function () {
    return 'Кiлькiсть клiєнтiв: ' . Client::count();
});

Route::get('/test-account/{id}', function ($id) {
    return Account::with('client')->find($id);
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

