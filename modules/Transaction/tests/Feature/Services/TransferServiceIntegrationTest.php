<?php

declare(strict_types=1);

namespace Modules\Transaction\tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Account\src\Infrastructure\Repositories\AccountRepository;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Shared\src\Domain\ValueObjects\Money;
use Modules\Transaction\src\Application\DTO\TransferDTO;
use Modules\Transaction\src\Application\Services\Monitoring\TransferErrorReporter;
use Modules\Transaction\src\Application\Services\TransferService;
use Modules\Transaction\src\Domain\Exceptions\InsufficientBalanceException;
use Modules\Transaction\src\Domain\Exceptions\SameAccountTransferException;
use Modules\Transaction\src\Infrastructure\Repositories\TransactionRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransferServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private TransferService $transferService;
    private AccountRepository $accountRepository;
    private TransactionRepository $transactionRepository;

    private TransferErrorReporter $errorReporter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountRepository = new AccountRepository();
        $this->transactionRepository = new TransactionRepository();
        $this->errorReporter = new TransferErrorReporter();

        $this->transferService = new TransferService(
            $this->accountRepository,
            $this->transactionRepository,
            $this->errorReporter
        );
    }

    #[Test]
    public function transfer_persists_transactions_and_updates_balances(): void
    {
        // Arrange
        /** @var Client $client */
        $client = Client::factory()->create();

        /** @var Account $fromAccount */
        $fromAccount = Account::factory()->create([
            'client_id' => $client->id,
            'account_number' => 'UA1234567890',
            'balance' => 10000.00,
            'currency' => 'UAH',
        ]);

        /** @var Account $toAccount */
        $toAccount = Account::factory()->create([
            'client_id' => $client->id,
            'account_number' => 'UA0987654321',
            'balance' => 5000.00,
            'currency' => 'UAH',
        ]);

        $amount = 2000;
        $currency = 'UAH';

        $dto = new TransferDTO(
            accountFromId: $fromAccount->id,
            accountToId: $toAccount->id,
            amount: new Money((string)$amount, $currency),
            description: 'Integration test transfer'
        );

        // Act
        $result = $this->transferService->executeTransfer($dto);

        // Assert
        $this->assertArrayHasKey('transaction_out_id', $result);
        $this->assertArrayHasKey('transaction_in_id', $result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertEquals($amount, $result['amount']);

        $fromAccountFresh = Account::find($fromAccount->id);
        $toAccountFresh = Account::find($toAccount->id);

        $this->assertEquals(8000.00, $fromAccountFresh->balance);
        $this->assertEquals(7000.00, $toAccountFresh->balance);

        // Assert
        $this->assertDatabaseHas('transactions', [
            'account_id' => $fromAccount->id,
            'amount' => -$amount,
            'type' => 'transfer_out',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $toAccount->id,
            'amount' => $amount,
            'type' => 'transfer_in',
            'status' => 'completed',
        ]);

        $this->assertDatabaseCount('transactions', 2);
    }

    #[Test]
    public function transfer_with_commission_persists_correct_amounts(): void
    {
        // Arrange
        /** @var Client $client */
        $client = Client::factory()->create();

        /** @var Account $fromAccount */
        $fromAccount = Account::factory()->create([
            'client_id' => $client->id,
            'account_number' => 'UA1111111111',
            'balance' => 50000.00,
            'currency' => 'UAH',
        ]);

        /** @var Account $toAccount */
        $toAccount = Account::factory()->create([
            'client_id' => $client->id,
            'account_number' => 'UA2222222222',
            'balance' => 10000.00,
            'currency' => 'UAH',
        ]);

        $amount = 15000;
        $commission = 75;
        $totalDeduct = $amount + $commission;
        $currency = 'UAH';

        $dto = new TransferDTO(
            accountFromId: $fromAccount->id,
            accountToId: $toAccount->id,
            amount: new Money((string)$amount, $currency),
            description: 'Integration test transfer with commission'
        );

        // Act
        $result = $this->transferService->executeTransfer($dto);

        $this->assertEquals($commission, $result['commission']);

        $fromAccountFresh = Account::find($fromAccount->id);
        $toAccountFresh = Account::find($toAccount->id);

        $this->assertEquals(50000 - $totalDeduct, $fromAccountFresh->balance);
        $this->assertEquals(10000 + $amount, $toAccountFresh->balance);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $fromAccount->id,
            'amount' => -$amount,
            'type' => 'transfer_out',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $toAccount->id,
            'amount' => $amount,
            'type' => 'transfer_in',
            'status' => 'completed',
        ]);
    }

    #[Test]
    public function transfer_fails_when_insufficient_balance(): void
    {
        // Arrange
        /** @var Client $client */
        $client = Client::factory()->create();

        /** @var Account $fromAccount */
        $fromAccount = Account::factory()->create([
            'client_id' => $client->id,
            'account_number' => 'UA3333333333',
            'balance' => 500.00,
            'currency' => 'UAH',
        ]);

        /** @var Account $toAccount */
        $toAccount = Account::factory()->create([
            'client_id' => $client->id,
            'account_number' => 'UA4444444444',
            'balance' => 1000.00,
            'currency' => 'UAH',
        ]);

        $amount = 1000;
        $currency = 'UAH';

        $dto = new TransferDTO(
            accountFromId: $fromAccount->id,
            accountToId: $toAccount->id,
            amount: new Money((string)$amount, $currency),
            description: 'Test transfer - insufficient balance'
        );

        // Act & Assert
        $this->expectException(InsufficientBalanceException::class);
        $this->transferService->executeTransfer($dto);

        // Assert
        $this->assertDatabaseCount('transactions', 0);

        $fromAccountFresh = Account::find($fromAccount->id);
        $toAccountFresh = Account::find($toAccount->id);

        $this->assertEquals(500.00, $fromAccountFresh->balance);
        $this->assertEquals(1000.00, $toAccountFresh->balance);
    }

    #[Test]
    public function transfer_fails_when_same_account(): void
    {
        // Arrange
        /** @var Client $client */
        $client = Client::factory()->create();

        /** @var Account $account */
        $account = Account::factory()->create([
            'client_id' => $client->id,
            'account_number' => 'UA5555555555',
            'balance' => 10000.00,
            'currency' => 'UAH',
        ]);

        $amount = 1000;
        $currency = 'UAH';

        $dto = new TransferDTO(
            accountFromId: $account->id,
            accountToId: $account->id,
            amount: new Money((string)$amount, $currency),
            description: 'Test transfer - same account'
        );

        // Act & Assert
        $this->expectException(SameAccountTransferException::class);
        $this->transferService->executeTransfer($dto);

        // Assert
        $this->assertDatabaseCount('transactions', 0);

        // Assert
        $accountFresh = Account::find($account->id);
        $this->assertEquals(10000.00, $accountFresh->balance);
    }
}
