<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Client;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->client = Client::factory()->create();
        $this->account = Account::factory()->create([
            'client_id' => $this->client->id,
            'balance' => 10000.00,
            'currency' => 'UAH',
        ]);

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
