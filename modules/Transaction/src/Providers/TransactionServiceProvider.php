<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Account\src\Domain\Repositories\AccountRepositoryInterface;
use Modules\Transaction\Application\Services\TransferService;
use Modules\Transaction\src\Application\Services\TransactionService;
use Modules\Transaction\src\Domain\Repositories\TransactionRepositoryInterface;
use Modules\Transaction\src\Infrastructure\Repositories\TransactionRepository;

// ← змінити на звичайний ServiceProvider

class TransactionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository
        $this->app->bind(
            TransactionRepositoryInterface::class,
            TransactionRepository::class
        );

        // Services
        $this->app->singleton(TransactionService::class, function ($app) {
            return new TransactionService($app->make(TransactionRepositoryInterface::class));
        });

        $this->app->singleton(TransferService::class, function ($app) {
            return new TransferService(
                $app->make(AccountRepositoryInterface::class),
                $app->make(TransactionRepositoryInterface::class)
            );
        });
    }
}
