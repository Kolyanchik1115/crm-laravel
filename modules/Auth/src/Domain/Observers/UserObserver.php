<?php

declare(strict_types=1);

namespace Modules\Auth\src\Domain\Observers;

use Modules\Auth\src\Domain\Entities\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Before user creation
     */
    public function creating(User $user): void
    {
        Log::info('Creating user', ['email' => $user->email]);
    }

    /**
     * After user creation
     */
    public function created(User $user): void
    {
        Log::info('User created', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->first_name . ' ' . $user->last_name,
        ]);
    }

    /**
     * After user update
     */
    public function updated(User $user): void
    {
        if ($user->getChanges()) {
            Log::info('User updated', [
                'user_id' => $user->id,
                'changes' => $user->getChanges(),
            ]);
        }
    }

    /**
     * Check admin deletion
     */
    public function deleting(User $user): void
    {
        if ($user->hasRole('ADMIN')) {
            $adminCount = User::countByRole('ADMIN');

            if ($adminCount <= 1) {
                throw new \Exception('Cannot delete the last administrator');
            }
        }
    }

    /**
     * After delete
     */
    public function deleted(User $user): void
    {
        $deletedBy = Auth::check() ? Auth::id() : null;

        Log::info('User deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
            'deleted_by' => $deletedBy,
        ]);
    }

    /**
     * Restore method
     */
    public function restored(User $user): void
    {
        Log::info('User restored', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
