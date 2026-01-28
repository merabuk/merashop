<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use App\Shared\Domain\Exception\InvalidStringException;

class InvalidUserFirstNameException extends InvalidUserValueObjectExceptionInterface
{
    public static function fromBaseException(InvalidStringException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
