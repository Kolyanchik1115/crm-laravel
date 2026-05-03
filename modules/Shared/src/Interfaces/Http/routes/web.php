<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

//TODO: Remove this route after creating the start page
Route::get('/', function () {
    return redirect()->route('login');
});

//Swagger
Route::get('/api-docs/openapi.yaml', function () {
    $yamlContent = file_get_contents(storage_path('api-docs/openapi.yaml'));
    return response($yamlContent, 200, [
        'Content-Type' => 'application/x-yaml',
    ]);
});
