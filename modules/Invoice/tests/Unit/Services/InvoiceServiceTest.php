<?php

declare(strict_types=1);

namespace Modules\Invoice\tests\Unit\Services;

use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Modules\Invoice\src\Application\DTO\CreateInvoiceDTO;
use Modules\Invoice\src\Application\DTO\InvoiceItemDTO;
use Modules\Invoice\src\Application\Services\InvoiceService;
use Modules\Invoice\src\Application\Services\Monitoring\InvoiceErrorReporter;
use Modules\Invoice\src\Domain\Entities\Invoice;
use Modules\Invoice\src\Domain\Repositories\InvoiceItemRepositoryInterface;
use Modules\Invoice\src\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Service\src\Domain\Repositories\ServiceRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    private MockInterface $invoiceRepository;
    private MockInterface $invoiceItemRepository;
    private MockInterface $serviceRepository;

    private MockInterface $errorReporter;

    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceRepository = Mockery::mock(InvoiceRepositoryInterface::class);
        $this->invoiceItemRepository = Mockery::mock(InvoiceItemRepositoryInterface::class);
        $this->serviceRepository = Mockery::mock(ServiceRepositoryInterface::class);

        $this->errorReporter = Mockery::mock(InvoiceErrorReporter::class);
        $this->invoiceService = new InvoiceService(
            $this->invoiceRepository,
            $this->invoiceItemRepository,
            $this->serviceRepository,
            $this->errorReporter
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function create_invoice_calculates_total_correctly_and_creates_invoice(): void
    {
        $clientId = 1;

        $items = [
            new InvoiceItemDTO(serviceId: 1, quantity: 2, unitPrice: 100.00),
            new InvoiceItemDTO(serviceId: 2, quantity: 1, unitPrice: 50.00),
        ];

        $expectedTotal = 250.00;

        $dto = new CreateInvoiceDTO(
            clientId: $clientId,
            items: $items,
            currency: 'UAH',
        );

        foreach ($items as $item) {
            $this->serviceRepository
                ->shouldReceive('exists')
                ->with($item->serviceId)
                ->once()
                ->andReturn(true);
        }

        $createdInvoice = new Invoice();
        $createdInvoice->id = 1;
        $createdInvoice->client_id = $clientId;
        $createdInvoice->invoice_number = 'INV-TEST';
        $createdInvoice->total_amount = $expectedTotal;
        $createdInvoice->status = 'draft';
        $createdInvoice->issued_at = now();

        $this->invoiceRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($clientId, $expectedTotal) {
                return $data['client_id'] === $clientId
                    && $data['total_amount'] === $expectedTotal
                    && $data['status'] === 'draft';
            }))
            ->andReturn($createdInvoice);

        $this->invoiceItemRepository
            ->shouldReceive('createMany')
            ->once()
            ->with(1, $items);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn ($callback) => $callback());

        $result = $this->invoiceService->createInvoice($dto);

        $this->assertInstanceOf(Invoice::class, $result);
        $this->assertEquals($expectedTotal, $result->total_amount);
        $this->assertEquals($clientId, $result->client_id);
    }

    #[Test]
    public function create_invoice_throws_exception_when_service_not_found(): void
    {
        $items = [
            new InvoiceItemDTO(serviceId: 999, quantity: 1, unitPrice: 100.00),
        ];

        $dto = new CreateInvoiceDTO(
            clientId: 1,
            items: $items,
            currency: 'UAH',
        );

        $this->serviceRepository
            ->shouldReceive('exists')
            ->with(999)
            ->once()
            ->andReturn(false);

        $this->invoiceRepository->shouldReceive('create')->never();
        $this->invoiceItemRepository->shouldReceive('createMany')->never();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Service with ID 999 not found');

        $this->invoiceService->createInvoice($dto);
    }
}
