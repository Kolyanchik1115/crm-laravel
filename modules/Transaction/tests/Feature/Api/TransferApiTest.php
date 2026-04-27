<?php

declare(strict_types=1);

namespace Modules\Transaction\tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Transaction\src\Domain\Entities\Transaction;
use Modules\Transaction\src\Interfaces\Http\Api\V1\TransferController;
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

        $this->registerTransferRoute();

        // create client
        /** @var Client $client */
        $client = Client::factory()->create();

        /** @var Account $fromAccount */
        $fromAccount = Account::factory()->create([
            'client_id' => $client->id,
            'balance' => 10000.00,
            'currency' => 'UAH',
        ]);
        $this->fromAccount = $fromAccount;

        /** @var Account $toAccount */
        $toAccount = Account::factory()->create([
            'client_id' => $client->id,
            'balance' => 0,
            'currency' => 'UAH',
        ]);
        $this->toAccount = $toAccount;
    }

    /**
     * Route registration for testing purposes.
     * This is necessary because the routes are cached in the application,
     * and we need to ensure that our test route is registered before the tests run
     */
    private function registerTransferRoute(): void
    {
        if (!$this->app->routesAreCached()) {
            Route::post('/api/v1/transfers', [TransferController::class, 'store']);
            Route::get('/api/v1/transfers', [TransferController::class, 'index']);
            Route::get('/api/v1/transfers/{id}', [TransferController::class, 'show']);
        }
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

    #[Test]
    public function transfer_index_returns_200_with_list(): void
    {
        /** @var Transaction $transaction1 */
        $transaction1 = Transaction::factory()->create([
            'account_id' => $this->fromAccount->id,
            'amount' => -100.00,
            'type' => 'transfer_out',
            'status' => 'completed',
            'description' => 'Outgoing transfer 1',
        ]);

        /** @var Transaction $transaction2 */
        $transaction2 = Transaction::factory()->create([
            'account_id' => $this->toAccount->id,
            'amount' => 100.00,
            'type' => 'transfer_out',
            'status' => 'completed',
            'description' => 'Incoming transfer 1',
        ]);

        /** @var Transaction $transaction3 */
        $transaction3 = Transaction::factory()->create([
            'account_id' => $this->fromAccount->id,
            'amount' => -50.00,
            'type' => 'transfer_out',
            'status' => 'completed',
            'description' => 'Outgoing transfer 2',
        ]);

        $response = $this->getJson('/api/v1/transfers');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'account_from_id',
                    'account_to_id',
                    'amount',
                    'currency',
                    'status',
                    'description',
                    'commission',
                    'created_at',
                ]
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
            ]
        ]);

        $this->assertCount(3, $response->json('data'));
    }

    #[Test]
    public function transfer_show_returns_200_when_exists(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()->create([
            'account_id' => $this->fromAccount->id,
            'amount' => -100.00,
            'type' => 'transfer_out',
            'status' => 'completed',
        ]);

        $response = $this->getJson("/api/v1/transfers/{$transaction->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $transaction->id);
        $response->assertJsonStructure([
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
            ]
        ]);
    }

    #[Test]
    public function transfer_show_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/v1/transfers/99999');

        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }
}
