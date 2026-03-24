<?php

declare(strict_types=1);

namespace App\Customer\Domain\Exception\CustomerProfile;

use App\Customer\Domain\Exception\InvalidCustomerValueObjectException;
use App\Shared\Domain\Exception\Services\Validation\InvalidPhoneNumberException;

final class InvalidCustomerProfilePhoneNumberException extends InvalidCustomerValueObjectException
{
    public static function fromBase(InvalidPhoneNumberException $e): self
    {
        return new self($e->getMessage(), previous: $e);
    }
}
