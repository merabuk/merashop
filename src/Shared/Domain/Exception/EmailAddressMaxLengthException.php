<?php

namespace App\Shared\Domain\Exception;

final class EmailAddressMaxLengthException extends InvalidEmailAddressException
{
    public static function becauseValueIsToLong(int $maxLength): self
    {
        return new self(sprintf('Email address is too long than %d characters', $maxLength));
    }
}
