<?php

declare(strict_types=1);

namespace Modules\Shared\src\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Modules\Auth\src\Domain\Entities\User;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local', 'staging', 'development');

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return $isLocal ||
                $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
    }

    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local', 'staging')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);
        Telescope::hideRequestHeaders(['cookie', 'x-csrf-token', 'x-xsrf-token']);
    }

    protected function gate(): void
    {
        if ($this->app->environment('local')) {
            Gate::define('viewTelescope', fn () => true);
            return;
        }

        Gate::define('viewTelescope', function (User $user) {
            return in_array($user->email, ['admin@crm.com'], true);
        });
    }
}
