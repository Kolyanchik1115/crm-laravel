<?php

declare(strict_types=1);

namespace Modules\Auth\src\Domain\Exceptions;

use DomainException;

class InsufficientRoleException extends DomainException
{
    protected array $requiredRoles = [];
    protected array $userRoles = [];

    public function __construct(
        array $requiredRoles = [],
        array $userRoles = [],
        string $message = "Недостатньо прав для виконання цієї дії"
    ) {
        parent::__construct($message, 403);
        $this->requiredRoles = $requiredRoles;
        $this->userRoles = $userRoles;
    }

    public function getRequiredRoles(): array
    {
        return $this->requiredRoles;
    }

    public function getUserRoles(): array
    {
        return $this->userRoles;
    }
}
