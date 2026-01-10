<?php

declare(strict_types=1);

namespace App\Customer\Domain\Exception\CustomerProfile;

use App\Customer\Domain\Exception\InvalidCustomerValueObjectException;
use App\Shared\Domain\Exception\InvalidStringException;

final class InvalidCustomerProfileLastNameException extends InvalidCustomerValueObjectException
{
    public static function fromBaseException(InvalidStringException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
