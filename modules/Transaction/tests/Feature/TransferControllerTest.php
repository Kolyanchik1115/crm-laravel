<?php

declare(strict_types=1);

namespace Modules\Transaction\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Client\src\Domain\Entities\Client;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransferControllerTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Account $fromAccount;
    private Account $toAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::factory()->create([
            'full_name' => 'Test Client',
            'email' => 'test@example.com',
            'balance' => 0,
            'currency' => 'UAH',
            'is_active' => true,
        ]);

        $this->fromAccount = Account::factory()->create([
            'client_id' => $this->client->id,
            'account_number' => 'UA1234567890',
            'balance' => 5000.00,
            'currency' => 'UAH',
        ]);

        $this->toAccount = Account::factory()->create([
            'client_id' => $this->client->id,
            'account_number' => 'UA0987654321',
            'balance' => 1000.00,
            'currency' => 'UAH',
        ]);
    }

    #[Test]
    public function transfer_endpoint_returns_success_and_creates_transactions_when_valid(): void
    {
        $amount = 1000;

        $response = $this->postJson('/api/v1/transfer', [
            'from_account_id' => $this->fromAccount->id,
            'to_account_id' => $this->toAccount->id,
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => 'Test transfer',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'transaction_out_id',
                'transaction_in_id',
                'amount',
                'commission',
                'created_at',
            ],
        ]);

        $response->assertJson([
            'success' => true,
            'message' => 'Переказ успішно виконано',
            'data' => [
                'amount' => $amount,
            ],
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $this->fromAccount->id,
            'amount' => -$amount,
            'type' => 'transfer_out',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $this->toAccount->id,
            'amount' => $amount,
            'type' => 'transfer_in',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $this->fromAccount->id,
            'balance' => 4000.00,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $this->toAccount->id,
            'balance' => 2000.00,
        ]);
    }

    #[Test]
    public function transfer_endpoint_returns_error_when_insufficient_balance(): void
    {
        $amount = 10000;

        $response = $this->postJson('/api/v1/transfer', [
            'from_account_id' => $this->fromAccount->id,
            'to_account_id' => $this->toAccount->id,
            'amount' => $amount,
            'currency' => 'UAH',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Insufficient funds for transfer',
        ]);

        $this->assertDatabaseCount('transactions', 0);
    }

    #[Test]
    public function transfer_endpoint_returns_error_when_same_account(): void
    {
        $response = $this->postJson('/api/v1/transfer', [
            'from_account_id' => $this->fromAccount->id,
            'to_account_id' => $this->fromAccount->id,
            'amount' => 100,
            'currency' => 'UAH',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['to_account_id']);
    }

    #[Test]
    public function transfer_endpoint_returns_error_when_account_not_found(): void
    {
        $response = $this->postJson('/api/v1/transfer', [
            'from_account_id' => 99999,
            'to_account_id' => $this->toAccount->id,
            'amount' => 100,
            'currency' => 'UAH',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['from_account_id']);
    }

    #[Test]
    public function transfer_endpoint_returns_validation_error_when_amount_missing(): void
    {
        $response = $this->postJson('/api/v1/transfer', [
            'from_account_id' => $this->fromAccount->id,
            'to_account_id' => $this->toAccount->id,
            'currency' => 'UAH',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function transfer_endpoint_returns_validation_error_when_amount_negative(): void
    {
        $response = $this->postJson('/api/v1/transfer', [
            'from_account_id' => $this->fromAccount->id,
            'to_account_id' => $this->toAccount->id,
            'amount' => -100,
            'currency' => 'UAH',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function transfer_endpoint_applies_commission_when_amount_above_threshold(): void
    {
        $amount = 15000;
        $commission = 75;

        $this->fromAccount->update(['balance' => 20000]);

        $response = $this->postJson('/api/v1/transfer', [
            'from_account_id' => $this->fromAccount->id,
            'to_account_id' => $this->toAccount->id,
            'amount' => $amount,
            'currency' => 'UAH',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'amount' => $amount,
                'commission' => $commission,
            ],
        ]);
    }
}
