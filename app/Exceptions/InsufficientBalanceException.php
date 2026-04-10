<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

class InsufficientBalanceException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Insufficient funds for transfer');
    }
}
