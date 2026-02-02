<?php

declare(strict_types=1);

namespace App\Customer\Domain\Exception\CustomerProfile;

use App\Customer\Domain\Exception\InvalidCustomerValueObjectException;

final class InvalidCustomerProfileIdException extends InvalidCustomerValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('The value is not a valid Customer profile ID');
    }
}
