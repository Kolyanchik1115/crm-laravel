<?php

declare(strict_types=1);

namespace Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Invoice\src\Interfaces\Http\Api\V1\InvoiceController;
use Modules\Service\src\Domain\Entities\Service;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Service $service1;
    private Service $service2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerInvoiceRoutes();

        /** @var Client $client */
        $client = Client::factory()->create();
        $this->client = $client;

        /** @var Service $service1 */
        $service1 = Service::factory()->create([
            'base_price' => 500.00,
            'currency' => 'UAH',
        ]);
        $this->service1 = $service1;

        /** @var Service $service2 */
        $service2 = Service::factory()->create([
            'base_price' => 300.00,
            'currency' => 'UAH',
        ]);
        $this->service2 = $service2;
    }

    private function registerInvoiceRoutes(): void
    {
        if (!$this->app->routesAreCached()) {
            Route::post('/api/v1/invoices', [InvoiceController::class, 'store']);
            Route::get('/api/v1/invoices/{id}', [InvoiceController::class, 'show']);
        }
    }

    #[Test]
    public function invoice_store_returns_201_when_valid(): void
    {

        $response = $this->postJson('/api/v1/invoices', [
            'client_id' => $this->client->id,
            'items' => [
                [
                    'service_id' => $this->service1->id,
                    'quantity' => 2,
                    'unit_price' => (string)$this->service1->base_price,
                ],
                [
                    'service_id' => $this->service2->id,
                    'quantity' => 1,
                    'unit_price' => (string)$this->service2->base_price,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'client_id',
                'total_amount',
                'currency',
                'status',
                'created_at',
            ],
            'success',
            'message',
        ]);

        $response->assertJson([
            'success' => true,
            'message' => 'Рахунок-фактуру успішно створено',
        ]);
    }

    #[Test]
    public function invoice_store_returns_422_when_items_invalid(): void
    {
        $data = [
            'client_id' => $this->client->id,
            'items' => [],
        ];

        $response = $this->postJson('/api/v1/invoices', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'items',
                ],
            ])
            ->assertJsonValidationErrors(['items']);
    }

    #[Test]
    public function invoice_show_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/v1/invoices/99999');

        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }

}
