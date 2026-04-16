<?php

declare(strict_types=1);

namespace Modules\Invoice\Domain\Repositories;

interface InvoiceItemRepositoryInterface
{
    public function createMany(int $invoiceId, array $items): void;
}
