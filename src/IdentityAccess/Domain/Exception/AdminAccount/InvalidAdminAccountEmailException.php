<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\AdminAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\Shared\Domain\Exception\Services\Validation\InvalidEmailAddressException;

final class InvalidAdminAccountEmailException extends InvalidIdentityAccessValueObjectException
{
    public static function fromBaseException(InvalidEmailAddressException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
