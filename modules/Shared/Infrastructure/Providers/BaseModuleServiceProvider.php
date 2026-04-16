<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

abstract class BaseModuleServiceProvider extends ServiceProvider
{
    abstract protected function getModuleName(): string;
    abstract protected function registerServices(): void;

    public function register(): void
    {
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->loadModuleRoutes();
    }

    protected function loadModuleRoutes(): void
    {
        $basePath = $this->getModuleBasePath();

        $webPath = $basePath . '/Interfaces/Http/routes/web.php';
        $apiPath = $basePath . '/Interfaces/Http/routes/api.php';

        if (file_exists($webPath)) {
            $this->loadRoutesFrom($webPath);
        }

        if (file_exists($apiPath)) {
            $this->loadRoutesFrom($apiPath);
        }
    }

    protected function getModuleBasePath(): string
    {
        return dirname((new \ReflectionClass($this))->getFileName(), 2);
    }
}
