<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use App\Shared\Domain\Exception\InvalidPhoneNumberException;

class InvalidUserPhoneNumberException extends InvalidUserValueObjectExceptionInterface
{
    public static function fromBaseException(InvalidPhoneNumberException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
