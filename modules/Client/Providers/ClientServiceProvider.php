<?php

declare(strict_types=1);

namespace Modules\Client\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Client\Application\Services\ClientService;
use Modules\Client\Infrastructure\Repositories\ClientRepository;

class ClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Services
        $this->app->singleton(ClientService::class, function ($app) {
            return new ClientService($app->make(ClientRepository::class));
        });
    }

    public function boot(): void
    {
        // routes
        $this->loadRoutesFrom(__DIR__ . '/../Interfaces/Http/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Interfaces/Http/routes/api.php');

        //  views
        $this->loadViewsFrom(__DIR__ . '/../Interfaces/views', 'clients');
    }
}
