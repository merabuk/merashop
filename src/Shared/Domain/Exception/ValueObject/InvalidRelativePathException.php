<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\ValueObject;

final class InvalidRelativePathException extends InvalidValueObjectExceptionInterface
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Relative path cannot be empty');
    }

    public static function becauseItContainsInvalidCharacters(): self
    {
        return new self('Relative path cannot contain invalid characters');
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Relative path is too long (max %d characters)', $maxLength));
    }

    public static function becauseItContainsForbiddenNames(): self
    {
        return new self('Relative path cannot contain forbidden names');
    }
}
