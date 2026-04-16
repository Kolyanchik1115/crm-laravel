<?php

declare(strict_types=1);

foreach (glob(base_path('modules/*/Interfaces/Http/routes/web.php')) as $routeFile) {
    require_once $routeFile;
}

//TODO: Remove this route after creating the start page
Route::get('/', function () {
    return view('welcome');
});
