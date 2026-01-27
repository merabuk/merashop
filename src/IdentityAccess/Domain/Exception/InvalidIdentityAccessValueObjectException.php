<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception;

use App\Shared\Domain\Exception\ThrowableValueObjectException;

abstract class InvalidIdentityAccessValueObjectException extends IdentityAccessDomainException implements ThrowableValueObjectException
{
    public function getErrorCode(): string
    {
        return 'IDENTITY_ACCESS_VALUE_OBJECT_ERROR';
    }
}
