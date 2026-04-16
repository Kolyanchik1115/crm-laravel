<?php

declare(strict_types=1);

namespace Modules\Transaction\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;  // ← змінити на звичайний ServiceProvider
use Modules\Transaction\Application\Services\TransactionService;
use Modules\Transaction\Application\Services\TransferService;
use Modules\Transaction\Infrastructure\Repositories\TransactionRepository;
use Modules\Transaction\Domain\Repositories\TransactionRepositoryInterface;
use Modules\Account\Domain\Repositories\AccountRepositoryInterface;
use Modules\Transaction\Domain\Events\TransferCompleted;
use Modules\Transaction\Application\Listeners\SendTransferConfirmationNotification;
use Modules\Transaction\Application\Listeners\LogTransferToAudit;

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

    public function boot(): void
    {
        // Events
        Event::listen(
            TransferCompleted::class,
            SendTransferConfirmationNotification::class
        );

        Event::listen(
            TransferCompleted::class,
            LogTransferToAudit::class
        );
    }
}
