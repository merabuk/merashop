<?php

declare(strict_types=1);

namespace App\Customer\Domain\Exception;

use App\Shared\Domain\Exception\ServerException;

abstract class CustomerDomainException extends ServerException implements ThrowableCustomerException
{
    public function getErrorCode(): string
    {
        return 'CUSTOMER_DOMAIN_ERROR';
    }
}
