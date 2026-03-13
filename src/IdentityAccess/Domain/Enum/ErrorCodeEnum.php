<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Enum;

enum ErrorCodeEnum: string
{
    case IdentityAccessDomainError = 'IDENTITY_ACCESS_DOMAIN_ERROR';
    case UserAccountAlreadyExists = 'USER_ACCOUNT_ALREADY_EXISTS';
    case AdminAccountAlreadyExists = 'ADMIN_ACCOUNT_ALREADY_EXISTS';
    case ModuleAccountAlreadyExists = 'MODULE_ACCOUNT_ALREADY_EXISTS';
}
