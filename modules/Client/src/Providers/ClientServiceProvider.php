<?php

declare(strict_types=1);

namespace Modules\Client\src\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Client\src\Application\Services\ClientService;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Client\src\Domain\Observers\ClientObserver;
use Modules\Client\src\Domain\Repositories\ClientRepositoryInterface;
use Modules\Client\src\Infrastructure\Repositories\ClientRepository;

class ClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Repository
        $this->app->bind(
            ClientRepositoryInterface::class,
            ClientRepository::class
        );

        // Service
        $this->app->singleton(ClientService::class, function ($app) {
            return new ClientService($app->make(ClientRepository::class));
        });

        //Observer
        $this->app->booted(function () {
            Client::observe(ClientObserver::class);
        });
    }
}
