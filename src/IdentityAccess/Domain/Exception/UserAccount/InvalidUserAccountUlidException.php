<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\UserAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidUserAccountUlidException extends InvalidIdentityAccessValueObjectException
{
    public static function becauseItIsNotAValidUlid(): self
    {
        return new self('Invalid user account ULID');
    }
}
