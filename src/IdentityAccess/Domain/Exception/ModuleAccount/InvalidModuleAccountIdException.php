<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\ModuleAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidModuleAccountIdException extends InvalidIdentityAccessValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('The value is not a valid Module account ID');
    }
}
