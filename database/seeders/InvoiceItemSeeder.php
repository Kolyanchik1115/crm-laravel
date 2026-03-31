<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Database\Seeder;

class InvoiceItemSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = Invoice::all();
        $services = Service::all();

        if ($services->isEmpty()) {
            $this->command->warn('Нема послуг, пропускаемо invoice_items');
            return;
        }

        foreach ($invoices as $invoice) {

            // Random items count
            $itemsCount = rand(1, 4);

            // Takes random service
            $randomServices = $services->random(min($itemsCount, $services->count()));

            foreach ($randomServices as $service) {
                $quantity = rand(1, 5);
                $unitPrice = $service->base_price;

                $invoice->services()->attach($service->id, [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);
            }

            // Updating the total invoice amount
            $total = $invoice->services->sum(function ($service) {
                return $service->pivot->quantity * $service->pivot->unit_price;
            });

            $invoice->update(['total_amount' => $total]);
        }
    }
}
