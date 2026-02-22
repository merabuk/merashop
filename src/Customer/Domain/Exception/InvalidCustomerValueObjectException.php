<?php

declare(strict_types=1);

namespace App\Customer\Domain\Exception;

use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;

abstract class InvalidCustomerValueObjectException extends CustomerDomainException implements ValueObjectExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'CUSTOMER_VALUE_OBJECT_ERROR';
    }
}
