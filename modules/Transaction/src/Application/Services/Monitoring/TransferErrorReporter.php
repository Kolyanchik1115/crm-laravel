<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Application\Services\Monitoring;

use Illuminate\Support\Facades\Log;
use Sentry\State\Scope;
use Throwable;

use function Sentry\captureException;
use function Sentry\withScope;

class TransferErrorReporter
{
    public function report(
        Throwable $e,
        int       $accountFromId,
        int       $accountToId,
        string    $amount,
        string    $currency,
        ?int      $transferId,
        ?string   $correlationId
    ): void {
        // Logging
        Log::error('Transfer failed: unexpected error', [
            'account_from_id' => $accountFromId,
            'account_to_id' => $accountToId,
            'amount' => $amount,
            'transfer_id' => $transferId,
            'correlation_id' => $correlationId,
            'error_message' => $e->getMessage(),
        ]);

        // Send in Sentry
        withScope(function (Scope $scope) use (
            $e,
            $accountFromId,
            $accountToId,
            $amount,
            $currency,
            $transferId,
            $correlationId
        ): void {
            $scope->setTag('module', 'transfers');
            $scope->setTag('action', 'execute');

            $scope->setExtra('account_from_id', $accountFromId);
            $scope->setExtra('account_to_id', $accountToId);
            $scope->setExtra('amount', $amount);
            $scope->setExtra('currency', $currency);
            $scope->setExtra('transfer_id', $transferId);

            if ($correlationId !== null) {
                $scope->setExtra('correlation_id', $correlationId);
            }

            captureException($e);
        });
    }
}
