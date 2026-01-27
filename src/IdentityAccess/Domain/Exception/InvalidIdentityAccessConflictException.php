<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception;

use App\Shared\Domain\Exception\ThrowableValueObjectException;

abstract class InvalidIdentityAccessConflictException extends IdentityAccessDomainException implements ThrowableValueObjectException
{
    public function getErrorCode(): string
    {
        return 'IDENTITY_ACCESS_CONFLICT_ERROR';
    }
}
