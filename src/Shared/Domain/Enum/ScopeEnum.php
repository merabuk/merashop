<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

enum ScopeEnum: string
{
    use StringEnumTrait;

    case UserRead = 'user:read';
    case UserWrite = 'user:write';
}
