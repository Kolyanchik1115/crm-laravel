<?php

declare(strict_types=1);

namespace Modules\Transaction\tests\Unit\Services;

use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Account\src\Domain\Repositories\AccountRepositoryInterface;
use Modules\Shared\src\Domain\ValueObjects\Money;
use Modules\Transaction\src\Application\DTO\TransferDTO;
use Modules\Transaction\src\Application\Services\Monitoring\TransferErrorReporter;
use Modules\Transaction\src\Application\Services\TransferService;
use Modules\Transaction\src\Domain\Entities\Transaction;
use Modules\Transaction\src\Domain\Exceptions\InsufficientBalanceException;
use Modules\Transaction\src\Domain\Exceptions\SameAccountTransferException;
use Modules\Transaction\src\Domain\Repositories\TransactionRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    private MockInterface $accountRepository;
    private MockInterface $transactionRepository;
    private MockInterface $errorReporter;
    private TransferService $transferService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountRepository = Mockery::mock(AccountRepositoryInterface::class);
        $this->transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
        $this->errorReporter = Mockery::mock(TransferErrorReporter::class);

        $this->transferService = new TransferService(
            $this->accountRepository,
            $this->transactionRepository,
            $this->errorReporter
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function executeTransfer_creates_transactions_when_balance_sufficient(): void
    {
        // Arrange
        $fromAccountId = 1;
        $toAccountId = 2;
        $amount = 5000;
        $currency = 'UAH';

        // Create real Account objects (not mocks) to avoid method issues
        $fromAccount = new Account();
        $fromAccount->id = $fromAccountId;
        $fromAccount->balance = 10000;
        $fromAccount->account_number = 'UA1234567890';
        $fromAccount->currency = $currency;

        $toAccount = new Account();
        $toAccount->id = $toAccountId;
        $toAccount->balance = 5000;
        $toAccount->account_number = 'UA0987654321';
        $toAccount->currency = $currency;

        $dto = new TransferDTO(
            accountFromId: $fromAccountId,
            accountToId: $toAccountId,
            amount: new Money((string)$amount, $currency),
            description: 'Test transfer'
        );

        $this->accountRepository
            ->shouldReceive('findById')
            ->with($fromAccountId)
            ->once()
            ->andReturn($fromAccount);

        $this->accountRepository
            ->shouldReceive('findById')
            ->with($toAccountId)
            ->once()
            ->andReturn($toAccount);

        $this->accountRepository
            ->shouldReceive('decrementBalance')
            ->with($fromAccountId, (string)$amount)
            ->once();

        $this->accountRepository
            ->shouldReceive('incrementBalance')
            ->with($toAccountId, (string)$amount)
            ->once();

        $this->transactionRepository
            ->shouldReceive('create')
            ->times(2)
            ->andReturn(
                new Transaction(['id' => 100]),
                new Transaction(['id' => 101])
            );

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                $callback();
            });

        // Act
        /** @var array $result */
        $result = $this->transferService->executeTransfer($dto);

        $this->assertArrayHasKey('transaction_out_id', $result);
        $this->assertArrayHasKey('transaction_in_id', $result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertArrayHasKey('commission', $result);
        $this->assertEquals($amount, $result['amount']);
    }

    #[Test]
    public function executeTransfer_throws_InsufficientBalanceException_when_balance_insufficient(): void
    {
        // Arrange
        $fromAccountId = 1;
        $toAccountId = 2;
        $amount = 500;
        $currency = 'UAH';

        $fromAccount = new Account();
        $fromAccount->id = $fromAccountId;
        $fromAccount->balance = 100;

        $toAccount = new Account();
        $toAccount->id = $toAccountId;
        $toAccount->balance = 5000;

        $dto = new TransferDTO(
            accountFromId: $fromAccountId,
            accountToId: $toAccountId,
            amount: new Money((string)$amount, $currency),
            description: 'Test transfer'
        );

        $this->accountRepository
            ->shouldReceive('findById')
            ->with($fromAccountId)
            ->once()
            ->andReturn($fromAccount);

        $this->accountRepository
            ->shouldReceive('findById')
            ->with($toAccountId)
            ->once()
            ->andReturn($toAccount);

        $this->transactionRepository
            ->shouldReceive('create')
            ->never();

        $this->accountRepository
            ->shouldReceive('decrementBalance')
            ->never();

        $this->accountRepository
            ->shouldReceive('incrementBalance')
            ->never();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                $callback();
            });

        // Act & Assert
        $this->expectException(InsufficientBalanceException::class);
        $this->transferService->executeTransfer($dto);
    }

    #[Test]
    public function executeTransfer_throws_SameAccountTransferException_when_accounts_identical(): void
    {
        // Arrange
        $accountId = 1;
        $amount = 100;
        $currency = 'UAH';

        $dto = new TransferDTO(
            accountFromId: $accountId,
            accountToId: $accountId, // Same account!
            amount: new Money((string)$amount, $currency),
            description: 'Test transfer'
        );

        $this->accountRepository
            ->shouldReceive('findById')
            ->never();

        $this->transactionRepository
            ->shouldReceive('create')
            ->never();

        // Act & Assert
        $this->expectException(SameAccountTransferException::class);
        $this->transferService->executeTransfer($dto);
    }

    #[Test]
    public function executeTransfer_applies_commission_when_amount_above_threshold(): void
    {
        // Arrange
        $fromAccountId = 1;
        $toAccountId = 2;
        $amount = 15000;
        $commission = 75; // 15000 * 0.5% = 75
        $totalDeduct = $amount + $commission;
        $currency = 'UAH';

        $fromAccount = new Account();
        $fromAccount->id = $fromAccountId;
        $fromAccount->balance = 20000;
        $fromAccount->account_number = 'UA1234567890';
        $fromAccount->currency = $currency;

        $toAccount = new Account();
        $toAccount->id = $toAccountId;
        $toAccount->balance = 5000;
        $toAccount->account_number = 'UA0987654321';
        $toAccount->currency = $currency;

        $dto = new TransferDTO(
            accountFromId: $fromAccountId,
            accountToId: $toAccountId,
            amount: new Money((string)$amount, $currency),
            description: 'Test transfer'
        );

        $this->accountRepository
            ->shouldReceive('findById')
            ->with($fromAccountId)
            ->once()
            ->andReturn($fromAccount);

        $this->accountRepository
            ->shouldReceive('findById')
            ->with($toAccountId)
            ->once()
            ->andReturn($toAccount);

        // Important: decrementBalance should be called with amount + commission
        $this->accountRepository
            ->shouldReceive('decrementBalance')
            ->with($fromAccountId, (string)$totalDeduct)
            ->once();

        $this->accountRepository
            ->shouldReceive('incrementBalance')
            ->with($toAccountId, (string)$amount)
            ->once();

        $this->transactionRepository
            ->shouldReceive('create')
            ->times(2)
            ->andReturn(
                new Transaction(['id' => 100]),
                new Transaction(['id' => 101])
            );

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                $callback();
            });

        // Act
        $result = $this->transferService->executeTransfer($dto);

        // Assert
        $this->assertEquals($commission, $result['commission']);
    }
}
