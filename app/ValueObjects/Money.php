<?php

declare(strict_types=1);

namespace App\ValueObjects;

final readonly class Money
{
    private float $amountValue;

    public function __construct(
        public string $amount,
        public string $currency = 'UAH'
    )
    {
        $this->amountValue = (float)$this->amount;

        if ($this->amountValue <= 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }

        if (strlen($this->currency) !== 3) {
            throw new \InvalidArgumentException('Currency must be 3-letter code.');
        }
    }

    public function isGreaterThan(string $threshold): bool
    {
        return $this->amountValue > (float)$threshold;
    }

    public function add(string $amount): self
    {
        $sum = $this->amountValue + (float)$amount;
        return new self((string)$sum, $this->currency);
    }

    public function getValue(): float
    {
        return $this->amountValue;
    }

    public function equals(Money $other): bool
    {
        return $this->amountValue === $other->getValue() && $this->currency === $other->currency;
    }
}
