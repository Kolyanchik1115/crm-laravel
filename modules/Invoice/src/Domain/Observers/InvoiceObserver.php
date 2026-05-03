<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Domain\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Invoice\src\Domain\Entities\Invoice;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        // Generate invoice number if not set
        if (empty($invoice->invoice_number)) {
            $invoice->invoice_number = $this->generateInvoiceNumber();
        }

        // Set default status
        if (empty($invoice->status)) {
            $invoice->status = 'draft';
        }

        // Set issued date if not set
        if (empty($invoice->issued_at)) {
            $invoice->issued_at = now();
        }

        Log::info('Creating invoice', [
            'client_id' => $invoice->client_id,
            'invoice_number' => $invoice->invoice_number,
        ]);
    }

    public function created(Invoice $invoice): void
    {

        Log::info('Invoice created', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'client_id' => $invoice->client_id,
            'total_amount' => $invoice->total_amount,
        ]);
    }

    public function updated(Invoice $invoice): void
    {
        // Log status changes
        if ($invoice->wasChanged('status')) {
            Log::info('Invoice status changed', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'old_status' => $invoice->getOriginal('status'),
                'new_status' => $invoice->status,
            ]);
        }

        // Log other changes
        if ($invoice->getChanges()) {
            Log::info('Invoice updated', [
                'invoice_id' => $invoice->id,
                'changes' => $invoice->getChanges(),
            ]);
        }
    }

    public function deleting(Invoice $invoice): void
    {
        if ($invoice->status === 'paid') {
            throw new \Exception('Cannot delete paid invoice');
        }
    }

    public function deleted(Invoice $invoice): void
    {
        Log::info('Invoice deleted', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'deleted_by' => Auth::id(),
            ]);
    }

    /**
     * Generate unique invoice number.
     * Format: INV-YYYYMMDD-XXXX
     */
    private function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "INV-{$date}-";

        // Get the last invoice number for today
        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = str_pad((string)$lastNumber, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }
}
