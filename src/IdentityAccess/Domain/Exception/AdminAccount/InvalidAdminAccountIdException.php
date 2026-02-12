<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\AdminAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidAdminAccountIdException extends InvalidIdentityAccessValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('The value is not a valid Admin account ID');
    }
}
