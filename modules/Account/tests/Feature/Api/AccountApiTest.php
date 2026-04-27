<?php

declare(strict_types=1);

namespace Modules\Account\tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Account\src\Interfaces\Http\Api\V1\AccountController;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Transaction\src\Domain\Entities\Transaction;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerAccountRoutes();

        /** @var Client $client */
        $client = Client::factory()->create();
        $this->client = $client;

        /** @var Account $account */
        $account = Account::factory()->create([
            'client_id' => $this->client->id,
            'balance' => 10000.00,
            'currency' => 'UAH',
        ]);
        $this->account = $account;
    }

    private function registerAccountRoutes(): void
    {
        if (!$this->app->routesAreCached()) {
            Route::get('/api/v1/accounts', [AccountController::class, 'index']);
            Route::get('/api/v1/accounts/{id}', [AccountController::class, 'show']);
            Route::get('/api/v1/accounts/{id}/transactions', [AccountController::class, 'transactions']);
        }
    }

    #[Test]
    public function account_index_returns_200(): void
    {
        Account::factory()->create([
            'client_id' => $this->client->id,
            'balance' => 5000.00,
        ]);

        Account::factory()->create([
            'client_id' => $this->client->id,
            'balance' => 3000.00,
        ]);

        $response = $this->getJson('/api/v1/accounts');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'client_id',
                    'balance',
                    'currency',
                    'created_at',
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));

    }

    #[Test]
    public function account_show_returns_200_with_balance(): void
    {
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}");

        $response->assertStatus(200);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'client_id',
                'balance',
                'currency',
                'created_at',
            ],
        ]);
    }

    #[Test]
    public function account_show_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/v1/accounts/99999');

        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }

    #[Test]
    public function account_transactions_returns_200(): void
    {
        Transaction::factory()->create([
            'account_id' => $this->account->id,
            'amount' => -100.00,
            'type' => 'transfer_out',
            'status' => 'completed',
        ]);

        Transaction::factory()->create([
            'account_id' => $this->account->id,
            'amount' => 200.00,
            'type' => 'deposit',
            'status' => 'completed',
        ]);

        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/transactions");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'account_id',
                    'amount',
                    'type',
                    'status',
                    'description',
                    'created_at',
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }
}
