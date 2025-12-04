<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use InvalidArgumentException;

class InvalidUserIdException extends InvalidArgumentException implements ThrowableUsersException
{
    public static function becauseItIsNotAValidUlid(string $invalidValue): self
    {
        return new self(sprintf('The string "%s" is not a valid User ID (ULID).', $invalidValue));
    }
}
