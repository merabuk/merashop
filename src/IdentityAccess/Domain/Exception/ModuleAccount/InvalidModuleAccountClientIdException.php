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

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Module account client id cannot exceed %d characters', $maxLength));
    }
}
