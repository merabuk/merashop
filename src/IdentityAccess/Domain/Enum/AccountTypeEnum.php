<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Enum;

use App\Shared\Domain\Enum\StringEnumTrait;

enum AccountTypeEnum: string
{
    use StringEnumTrait;

    case User = 'user';
    case Module = 'module';
}
