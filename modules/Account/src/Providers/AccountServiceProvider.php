<?php

declare(strict_types=1);

namespace Modules\Account\src\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Account\src\Application\Services\AccountService;
use Modules\Account\src\Domain\Repositories\AccountRepositoryInterface;
use Modules\Account\src\Infrastructure\Repositories\AccountRepository;

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
}
