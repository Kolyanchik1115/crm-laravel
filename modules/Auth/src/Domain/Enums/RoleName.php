<?php

declare(strict_types=1);

namespace Modules\Auth\src\Domain\Enums;

enum RoleName: string
{
    case USER = 'USER';
    case ADMIN = 'ADMIN';

    public function getLabel(): string
    {
        return match ($this) {
            self::USER => 'Користувач',
            self::ADMIN => 'Адміністратор',
        };
    }
}
