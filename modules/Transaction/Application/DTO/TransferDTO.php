<?php

declare(strict_types=1);

namespace Modules\Transaction\Application\DTO;

use Modules\Shared\Domain\ValueObjects\Money;

final readonly class TransferDTO
{
    public function __construct(
        public int    $accountFromId,
        public int    $accountToId,
        public Money  $amount,
        public string $description = '',
    ) {
    }

    public function toArray(): array
    {
        return [
            'account_from_id' => $this->accountFromId,
            'account_to_id' => $this->accountToId,
            'amount' => $this->amount->amount,
            'currency' => $this->amount->currency,
            'description' => $this->description,
        ];
    }
}
