<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\ModuleAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidModuleAccountPasswordHashException extends InvalidIdentityAccessValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('The password hash cannot be empty.');
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('The password hash cannot be longer than %d characters.', $maxLength));
    }
}
