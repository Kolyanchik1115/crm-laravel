<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class TransactionDTO
{
    public function __construct(
        public int     $accountId,
        public float   $amount,
        public string  $type,
        public string  $status,
        public ?string $description = null,
    ) {
    }

    public static function fromDTO(object $transaction): self
    {
        return new self(
            accountId: $transaction->account_id,
            amount: (float)$transaction->amount,
            type: $transaction->type,
            status: $transaction->status,
            description: $transaction->description,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => null,
            'account_id' => $this->accountId,
            'amount' => $this->amount,
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
        ];
    }
}
