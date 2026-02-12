<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\AdminAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidAdminAccountUlidException extends InvalidIdentityAccessValueObjectException
{
    public static function becauseItIsNotAValidUlid(): self
    {
        return new self('Invalid admin account ULID');
    }
}
