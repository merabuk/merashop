<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\UserAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidUserAccountPasswordHashException extends InvalidIdentityAccessValueObjectException
{
    public static function becauseItEmpty(): self
    {
        return new self('User account password hash cannot be empty');
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('User account password hash cannot be longer than %d characters', $maxLength));
    }
}
