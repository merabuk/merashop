<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\ValueObject\Translation;

use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Exception\ValueObject\InvalidValueObjectExceptionInterface;

final class InvalidTranslationNameException extends InvalidValueObjectExceptionInterface
{
    public static function fromBaseException(InvalidStringException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
