<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Enum;

enum ErrorCodeEnum: string
{
    case IdentityAccessDomainError = 'identity_access_domain_error';
    case UserAccountAlreadyExists = 'userAccount_alreadyExists';
    case AdminAccountAlreadyExists = 'adminAccount_alreadyExists';
    case ModuleAccountAlreadyExists = 'moduleAccount_alreadyExists';
}
