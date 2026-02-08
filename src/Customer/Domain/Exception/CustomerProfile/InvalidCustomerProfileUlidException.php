<?php

declare(strict_types=1);

namespace App\Customer\Domain\Exception\CustomerProfile;

use App\Customer\Domain\Exception\InvalidCustomerValueObjectException;

final class InvalidCustomerProfileUlidException extends InvalidCustomerValueObjectException
{
    public static function becauseItIsNotAValidUlid(): self
    {
        return new self('Invalid customer profile ULID');
    }
}
