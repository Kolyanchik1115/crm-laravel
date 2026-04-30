<?php

declare(strict_types=1);

namespace Modules\Auth\src\Domain\Exceptions;

use DomainException;

class ForbiddenException extends DomainException
{
    public function __construct(string $message = "Доступ заборонено")
    {
        parent::__construct($message, 403);
    }
}
