<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use InvalidArgumentException;

class InvalidEmailAddressException extends InvalidArgumentException implements ThrowableUsersException
{
    public static function becauseItIsNotValidEmailAddress(string $invalidValue): self
    {
        return new self(sprintf('The email address "%s" is not valid.', $invalidValue));
    }
}
