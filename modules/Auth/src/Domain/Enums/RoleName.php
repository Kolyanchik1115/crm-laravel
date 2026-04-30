<?php

declare(strict_types=1);

namespace Modules\Auth\src\Domain\Enums;

enum RoleName: string
{
    case USER = 'USER';
    case ADMIN = 'ADMIN';
    case MANAGER = 'MANAGER';

    public function getLabel(): string
    {
        return match ($this) {
            self::USER => 'Користувач',
            self::ADMIN => 'Адміністратор',
            self::MANAGER => 'Менеджер',
        };
    }

    //method for convert enum values into string
    public static function extractValue($role): ?string
    {
        if ($role instanceof self) {
            return $role->value;
        }

        if (is_string($role)) {
            return $role;
        }

        if (is_object($role) && property_exists($role, 'name')) {
            if ($role->name instanceof self) {
                return $role->name->value;
            }
            return $role->name;
        }

        return null;
    }
}
