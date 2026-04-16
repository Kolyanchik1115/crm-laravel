<?php

declare(strict_types=1);

namespace Modules\Account\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Account\Application\Services\AccountService;
use Modules\Account\Infrastructure\Repositories\AccountRepository;
use Modules\Account\Domain\Repositories\AccountRepositoryInterface;

class AccountServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository
        $this->app->bind(
            AccountRepositoryInterface::class,
            AccountRepository::class
        );

        // Service
        $this->app->singleton(AccountService::class, function ($app) {
            return new AccountService($app->make(AccountRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
