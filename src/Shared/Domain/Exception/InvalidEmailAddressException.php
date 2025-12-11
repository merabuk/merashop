<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

final class InvalidEmailAddressException extends InvalidArgumentException
{
    public static function becauseItIsNotValidEmailAddress(string $invalidValue): self
    {
        return new self(sprintf('The email address "%s" is not valid', $invalidValue));
    }
}
