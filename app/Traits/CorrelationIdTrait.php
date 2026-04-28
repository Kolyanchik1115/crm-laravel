<?php

declare(strict_types=1);

namespace App\Traits;

trait CorrelationIdTrait
{
    /**
     * Get correlation_id from request attributes
     */
    protected function getCorrelationId(): ?string
    {
        return request()->attributes->get('correlation_id');
    }
}
