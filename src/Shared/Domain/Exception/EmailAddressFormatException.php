<?php

namespace App\Shared\Domain\Exception;

final class EmailAddressFormatException extends InvalidEmailAddressException
{
    public static function becauseItIsNotValidEmailAddress(string $invalidValue): self
    {
        return new self(sprintf('The email address "%s" is not valid', $invalidValue));
    }
}
