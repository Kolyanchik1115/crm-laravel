<?php

declare(strict_types=1);

namespace Modules\Auth\src\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\src\Domain\Entities\User;
use Modules\Auth\src\Domain\Enums\RoleName;
use Modules\Auth\src\Domain\Exceptions\InsufficientRoleException;

class WebRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user instanceof User) {
            return redirect()->route('login');
        }

        $user->load('roles');

        $userRoles = $user->roles->pluck('name')->map(function ($role) {
            return RoleName::extractValue($role);
        })->toArray();

        foreach ($roles as $role) {
            if (in_array($role, $userRoles, true)) {
                return $next($request);
            }
        }

        throw new InsufficientRoleException($roles, $userRoles);
    }
}
