<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Services;

use App\Shared\Domain\Exception\InvalidPhoneNumberException;

final class PhoneNumberFormatException extends InvalidPhoneNumberException
{
    public static function becauseItIsNotValidPhoneNumberFormat(string $invalidValue): self
    {
        return new self(sprintf('The phone number "%s" format is not valid', $invalidValue));
    }
}
