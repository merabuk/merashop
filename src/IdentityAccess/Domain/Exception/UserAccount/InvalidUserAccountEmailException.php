<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\UserAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\Shared\Domain\Exception\InvalidEmailAddressException;

final class InvalidUserAccountEmailException extends InvalidIdentityAccessValueObjectException
{
    public static function fromBaseException(InvalidEmailAddressException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
