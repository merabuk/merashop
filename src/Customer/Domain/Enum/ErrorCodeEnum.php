<?php

declare(strict_types=1);

namespace App\Customer\Domain\Enum;

enum ErrorCodeEnum: string
{
    case CustomerDomainError = 'customer_domain_error';
    case CustomerProfileNotFound = 'customerProfile_notFound';
}
