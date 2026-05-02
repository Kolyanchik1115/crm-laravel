<?php

declare(strict_types=1);

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Log;
use Sentry\State\Scope;
use Throwable;

use function Sentry\captureException;
use function Sentry\withScope;

class InvoiceErrorReporter
{
    public function report(
        Throwable $e,
        int $clientId,
        ?int $invoiceId,
        ?string $totalAmount,
        ?string $correlationId
    ): void {
        // Logging
        Log::error('Invoice creation failed: unexpected error', [
            'client_id' => $clientId,
            'invoice_id' => $invoiceId,
            'total_amount' => $totalAmount,
            'correlation_id' => $correlationId,
            'error_message' => $e->getMessage(),
        ]);

        // Sent in Sentry
        withScope(function (Scope $scope) use (
            $e,
            $clientId,
            $invoiceId,
            $totalAmount,
            $correlationId
        ): void {
            $scope->setTag('module', 'invoices');
            $scope->setTag('action', 'create');

            $scope->setExtra('client_id', $clientId);
            $scope->setExtra('invoice_id', $invoiceId);
            $scope->setExtra('total_amount', $totalAmount);

            if ($correlationId !== null) {
                $scope->setExtra('correlation_id', $correlationId);
            }

            captureException($e);
        });
    }
}
