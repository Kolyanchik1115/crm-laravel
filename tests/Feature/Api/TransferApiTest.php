<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TransferApiTest extends TestCase
{
    use RefreshDatabase;

    private Account $fromAccount;
    private Account $toAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // create client
        $client = Client::factory()->create();

        // create account
        $this->fromAccount = Account::factory()->create([
            'client_id' => $client->id,
            'balance' => 10000.00,
            'currency' => 'UAH',
        ]);

        $this->toAccount = Account::factory()->create([
            'client_id' => $client->id,
            'balance' => 0,
            'currency' => 'UAH',
        ]);
    }

    #[Test]
    public function transfer_returns_201_with_resource_structure_when_valid(): void
    {
        $response = $this->postJson('/api/v1/transfers', [
            'account_from_id' => $this->fromAccount->id,
            'account_to_id' => $this->toAccount->id,
            'amount' => '100.00',
            'currency' => 'UAH',
            'description' => 'Тестовий переказ',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'account_from_id',
                    'account_to_id',
                    'amount',
                    'currency',
                    'status',
                    'description',
                    'commission',
                    'created_at',
                ],
                'success',
                'message',
            ]);

        $response->assertJson([
            'success' => true,
            'message' => 'Переказ успішно виконано',
            'data' => [
                'account_from_id' => $this->fromAccount->id,
                'account_to_id' => $this->toAccount->id,
                'amount' => 100.00,
                'currency' => 'UAH',
            ],
        ]);

        // check Location header
        $response->assertHeader('Location');
        $response->assertHeaderContains('Location', '/api/v1/transfers/');
    }

    #[Test]
    public function transfer_returns_422_with_errors_structure_when_validation_fails(): void
    {
        $response = $this->postJson('/api/v1/transfers', [
            'account_from_id' => $this->fromAccount->id,
            'account_to_id' => $this->toAccount->id,
            'amount' => '-100.00',
            'currency' => 'UAH',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'amount',
                ],
            ])
            ->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function transfer_returns_422_with_code_when_insufficient_balance(): void
    {
        $response = $this->postJson('/api/v1/transfers', [
            'account_from_id' => $this->fromAccount->id,
            'account_to_id' => $this->toAccount->id,
            'amount' => '999999.00',
            'currency' => 'UAH',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'INSUFFICIENT_BALANCE')
            ->assertJsonPath('message', 'Недостатньо коштів на рахунку');
    }

    #[Test]
    public function transfer_returns_422_with_code_when_same_accounts(): void
    {
        $response = $this->postJson('/api/v1/transfers', [
            'account_from_id' => $this->fromAccount->id,
            'account_to_id' => $this->fromAccount->id,
            'amount' => '100.00',
            'currency' => 'UAH',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'account_to_id',
                ],
            ])
            ->assertJsonValidationErrors(['account_to_id']);
    }

    #[Test]
    public function transfer_returns_422_when_invalid_currency(): void
    {
        $response = $this->postJson('/api/v1/transfers', [
            'account_from_id' => $this->fromAccount->id,
            'account_to_id' => $this->toAccount->id,
            'amount' => '100.00',
            'currency' => 'BTC',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    }

    #[Test]
    public function transfer_returns_422_when_account_not_found(): void
    {
        $response = $this->postJson('/api/v1/transfers', [
            'account_from_id' => 99999,
            'account_to_id' => $this->toAccount->id,
            'amount' => '100.00',
            'currency' => 'UAH',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_from_id']);
    }

    #[Test]
    public function transfer_returns_422_when_amount_has_more_than_2_decimals(): void
    {
        $response = $this->postJson('/api/v1/transfers', [
            'account_from_id' => $this->fromAccount->id,
            'account_to_id' => $this->toAccount->id,
            'amount' => '100.123',
            'currency' => 'UAH',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }
}
