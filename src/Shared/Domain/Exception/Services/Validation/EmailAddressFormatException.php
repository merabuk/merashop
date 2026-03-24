<?php

namespace App\Shared\Domain\Exception\Services\Validation;

final class EmailAddressFormatException extends InvalidEmailAddressException
{
    public static function becauseItIsNotValidEmailAddress(string $invalidValue): self
    {
        return new self(sprintf('The email address "%s" is not valid', $invalidValue));
    }

    public static function becauseLocalPartIsTooLong(int $maxLength): self
    {
        return new self(sprintf('The local part of the email address is too long (max %d characters before @)', $maxLength));
    }
}
