<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

enum RoleEnum: string
{
    use StringEnumTrait;

    case User = 'ROLE_USER';
    case Customer = 'ROLE_CUSTOMER';
}
