<?php

declare(strict_types=1);

namespace Modules\Transaction\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Transaction\Application\Services\TransactionService;
use Modules\Transaction\Infrastructure\Repositories\TransactionRepository;
use Modules\Transaction\Domain\Repositories\TransactionRepositoryInterface;

class TransactionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Repositories
        $this->app->bind(
            TransactionRepositoryInterface::class,
            TransactionRepository::class
        );

        //Services
        $this->app->singleton(TransactionService::class, function ($app) {
            return new TransactionService($app->make(TransactionRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        //routes
        $this->loadRoutesFrom(__DIR__ . '/../Interfaces/Http/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../Interfaces/Http/routes/web.php');

        //views
        $this->loadViewsFrom(__DIR__ . '/../Interfaces/views', 'transactions');
    }
}
