<?php

namespace App\Shared\Domain\Exception\ValueObject;

use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Exception\ThrowableValueObjectException;

abstract class InvalidValueObjectException extends LogicException implements ThrowableValueObjectException
{
    public function getErrorCode(): string
    {
        return 'SHARED_VALUE_OBJECT_ERROR';
    }
}
