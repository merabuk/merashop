<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

final class InvalidPhoneNumberException extends InvalidArgumentException
{
    public static function becauseItIsNotValidPhoneNumberFormat(string $invalidValue): self
    {
        return new self(sprintf('The phone number "%s" format is not valid', $invalidValue));
    }
}
