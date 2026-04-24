<?php

declare(strict_types=1);

namespace Modules\Service\src\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Service\src\Domain\Repositories\ServiceRepositoryInterface;
use Modules\Service\src\Infrastructure\Repositories\ServiceRepository;

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
}
