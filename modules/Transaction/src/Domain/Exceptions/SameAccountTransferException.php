<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Domain\Exceptions;

use Exception;

class SameAccountTransferException extends Exception
{
    public function __construct(string $message = "Рахунок відправника і одержувача не можуть збігатися")
    {
        parent::__construct($message);
    }
}
