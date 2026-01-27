<?php

declare(strict_types=1);

namespace App\Customer\Domain\Exception;

use App\Shared\Domain\Exception\ThrowableValueObjectException;

abstract class InvalidCustomerValueObjectException extends CustomerDomainException implements ThrowableValueObjectException
{
    public function getErrorCode(): string
    {
        return 'CUSTOMER_VALUE_OBJECT_ERROR';
    }
}
