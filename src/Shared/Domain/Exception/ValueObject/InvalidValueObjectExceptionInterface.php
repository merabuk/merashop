<?php

namespace App\Shared\Domain\Exception\ValueObject;

use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;

abstract class InvalidValueObjectExceptionInterface extends LogicException implements ValueObjectExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'SHARED_VALUE_OBJECT_ERROR';
    }
}
