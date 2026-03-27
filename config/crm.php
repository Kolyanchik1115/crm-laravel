<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | CRM Settings
    |--------------------------------------------------------------------------
    |
    | These settings are used throughout the CRM application.
    |
    */

    'default_currency' => env('CRM_DEFAULT_CURRENCY', 'UAH'),
    'min_transfer' => (float) env('CRM_MIN_TRANSFER', 10),
    'max_transfer' => (float) env('CRM_MAX_TRANSFER', 500000),
];
