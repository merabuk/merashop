<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\UserAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectExceptionInterface;

final class InvalidUserAccountIdException extends InvalidIdentityAccessValueObjectExceptionInterface
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('The value is not a valid User account ID');
    }
}
