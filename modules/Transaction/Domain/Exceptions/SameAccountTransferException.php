<?php

declare(strict_types=1);

namespace Modules\Transaction\Domain\Exceptions;

use DomainException;

class SameAccountTransferException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot transfer to the same account');
    }
}
