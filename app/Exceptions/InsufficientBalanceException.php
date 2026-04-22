<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

class InsufficientBalanceException extends DomainException
{
    public function __construct(string $message = "Недостатньо коштів на рахунку")
    {
        parent::__construct($message);
    }
}
