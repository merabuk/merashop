<?php

declare(strict_types=1);

namespace App\Customer\Domain\Enum;

enum ErrorCodeEnum: string
{
    case CustomerDomainError = 'CUSTOMER_DOMAIN_ERROR';
    case CustomerProfileNotFound = 'CUSTOMER_PROFILE_NOT_FOUND';
}
