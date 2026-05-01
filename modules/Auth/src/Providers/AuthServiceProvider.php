<?php

declare(strict_types=1);

namespace Modules\Auth\src\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\src\Application\Services\AuthService;
use Modules\Auth\src\Domain\Entities\User;
use Modules\Auth\src\Domain\Observers\UserObserver;
use Modules\Auth\src\Domain\Repositories\UserRepositoryInterface;
use Modules\Auth\src\Domain\Repositories\RoleRepositoryInterface;
use Modules\Auth\src\Infrastructure\Repositories\UserRepository;
use Modules\Auth\src\Infrastructure\Repositories\RoleRepository;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);

        // Bind service
        $this->app->singleton(AuthService::class);

        //Observer
        $this->app->booted(function () {
            User::observe(UserObserver::class);
        });
    }
}
