<?php

declare(strict_types=1);

namespace Modules\Dashboard\Application\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Client\Domain\Entities\Client;
use Modules\Invoice\Domain\Entities\Invoice;
use Modules\Transaction\Domain\Entities\Transaction;

class UpdateDashboardCacheJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 15;

    public function __construct(
        private readonly ?string $cacheKey = null
    ) {
    }

    public function handle(): void
    {
        $key = $this->cacheKey ?? 'crm:dashboard:stats';

        try {
            Log::info('UpdateDashboardCacheJob: Started', [
                'job' => self::class,
                'cache_key' => $key,
            ]);

            // invalidate cache
            Cache::forget($key);

            // new data from db
            $stats = [
                'clients_count' => Client::count(),
                'active_clients_count' => Client::where('is_active', true)->count(),
                'inactive_clients_count' => Client::where('is_active', false)->count(),
                'transactions_today' => Transaction::whereDate('created_at', today()->toDateString())
                    ->sum('amount'),
                'transactions_total' => Transaction::sum('amount'),
                'invoices_today' => Invoice::whereDate('created_at', today()->toDateString())->count(),
                'invoices_total' => Invoice::count(),
                'invoices_total_amount' => Invoice::sum('total_amount'),
                'updated_at' => now()->toIso8601String(),
            ];

            // new cache for 15 min
            Cache::put($key, $stats, now()->addMinutes(15));

            Log::info('UpdateDashboardCacheJob: completed', [
                'cache_key' => $key,
                'clients_count' => $stats['clients_count'],
                'transactions_today' => $stats['transactions_today'],
            ]);
        } catch (\Exception $e) {
            Log::error('UpdateDashboardCacheJob: Failed', [
                'job' => self::class,
                'cache_key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
