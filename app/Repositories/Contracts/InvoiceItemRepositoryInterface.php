<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface InvoiceItemRepositoryInterface
{
    public function createMany(int $invoiceId, array $items): void;
}
