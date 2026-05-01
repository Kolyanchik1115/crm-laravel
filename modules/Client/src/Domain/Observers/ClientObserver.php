<?php

declare(strict_types=1);

namespace Modules\Client\src\Domain\Observers;

use Modules\Client\src\Domain\Entities\Client;
use Modules\Account\src\Domain\Entities\Account;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ClientObserver
{
    public function creating(Client $client): void
    {
        // Set default values
        if (empty($client->balance)) {
            $client->balance = 0;
        }

        if (empty($client->currency)) {
            $client->currency = 'UAH';
        }

        if (empty($client->is_active)) {
            $client->is_active = true;
        }

        Log::info('Creating client', ['email' => $client->email]);
    }

    public function created(Client $client): void
    {
        $account = Account::create([
            'client_id' => $client->id,
            'account_number' => 'UA' . Str::random(28),
            'balance' => $client->balance,
            'currency' => $client->currency,
        ]);

        Cache::forget('clients:list');

        Log::info('Client created', [
            'client_id' => $client->id,
            'email' => $client->email,
            'full_name' => $client->full_name,
            'account_id' => $account->id,
            'account_number' => $account->account_number,
        ]);
    }

    public function updated(Client $client): void
    {
        Cache::forget('clients:list');

        // Log changes
        if ($client->getChanges()) {
            Log::info('Client updated', [
                'client_id' => $client->id,
                'changes' => $client->getChanges(),
            ]);
        }
    }

    public function deleting(Client $client): void
    {
        // Check if client has accounts with balance
        $accountsWithBalance = $client->accounts()->where('balance', '>', 0)->count();

        if ($accountsWithBalance > 0) {
            throw new \Exception('Cannot delete client with non-zero balance accounts');
        }
    }

    public function deleted(Client $client): void
    {
        // Clear cache
        Cache::forget('clients:list');

        Log::info('Client deleted', [
            'client_id' => $client->id,
            'email' => $client->email,
            'deleted_by' => auth()->id(),
        ]);
    }

    public function restored(Client $client): void
    {
        Cache::forget('clients:list');

        Log::info('Client restored', [
            'client_id' => $client->id,
            'email' => $client->email,
        ]);
    }
}
