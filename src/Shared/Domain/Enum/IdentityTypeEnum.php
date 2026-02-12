<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

enum IdentityTypeEnum: string
{
    use StringEnumTrait;

    case User = 'user';
    case Module = 'module';
    case Admin = 'admin';
}
