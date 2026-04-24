<?php

declare(strict_types=1);

//TODO: Remove this route after creating the start page
Route::get('/', function () {
    return view('shared::welcome');
});
