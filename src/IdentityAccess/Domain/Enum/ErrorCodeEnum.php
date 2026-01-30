<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Enum;

enum ErrorCodeEnum: string
{
    case UserAccountAlreadyExists = 'USER_ACCOUNT_ALREADY_EXISTS';
}
