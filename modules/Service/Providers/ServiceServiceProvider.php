<?php

declare(strict_types=1);

namespace Modules\Service\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Service\Domain\Repositories\ServiceRepositoryInterface;
use Modules\Service\Infrastructure\Repositories\ServiceRepository;

class ServiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository
        $this->app->bind(
            ServiceRepositoryInterface::class,
            ServiceRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
