<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use App\Shared\Domain\Exception\InvalidEmailAddressException;

class InvalidUserEmailAddressException extends InvalidUserValueObjectException
{
    public static function fromBaseException(InvalidEmailAddressException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
