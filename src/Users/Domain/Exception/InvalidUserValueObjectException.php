<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use App\Shared\Domain\Exception\ThrowableValueObjectException;

abstract class InvalidUserValueObjectException extends UserDomainException implements ThrowableValueObjectException
{
    public function getErrorCode(): string
    {
        return 'USERS_VALUE_OBJECT_ERROR';
    }
}
