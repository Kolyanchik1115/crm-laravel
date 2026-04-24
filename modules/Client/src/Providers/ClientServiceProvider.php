<?php

declare(strict_types=1);

namespace Modules\Client\src\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Client\src\Application\Services\ClientService;
use Modules\Client\src\Infrastructure\Repositories\ClientRepository;

class ClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Service
        $this->app->singleton(ClientService::class, function ($app) {
            return new ClientService($app->make(ClientRepository::class));
        });
    }
}
