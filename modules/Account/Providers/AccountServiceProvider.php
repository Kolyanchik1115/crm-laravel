<?php

declare(strict_types=1);

namespace Modules\Account\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Account\Domain\Repositories\AccountRepositoryInterface;
use Modules\Account\Infrastructure\Repositories\AccountRepository;

class AccountServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AccountRepositoryInterface::class,
            AccountRepository::class
        );
    }

    public function boot(): void
    {
        //routes
        $this->loadRoutesFrom(__DIR__ . '/../Interfaces/Http/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../Interfaces/Http/routes/web.php');

        //views
        $this->loadViewsFrom(__DIR__ . '/../Interfaces/views', 'accounts');
    }
}
