<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\ModuleAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidModuleAccountClientIdException extends InvalidIdentityAccessValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Module account client id cannot be empty');
    }

    public static function becauseItIsTooShort(int $minLength): self
    {
        return new self(sprintf('Module account client id must be at least %d characters long', $minLength));
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Module account client id cannot exceed %d characters', $maxLength));
    }
}
