<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

Route::prefix('v1')->middleware(['auth:api', 'role:ADMIN,MANAGER,USER'])->group(function () {
    // dashboard stats
    Route::get('/dashboard-stats', function () {
        $stats = Cache::get('crm:dashboard:stats');

        if (!$stats) {
            return response()->json([
                'status' => 'cache_miss',
                'message' => 'Cache is empty, job not executed yet',
            ]);
        }

        return response()->json([
            'status' => 'cache_hit',
            'data' => $stats,
        ]);
    });
});
