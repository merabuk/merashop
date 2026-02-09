<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\ValueObject;

final class InvalidLocaleException extends InvalidValueObjectExceptionInterface
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Locale cannot be empty');
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Locale is too long (max %d characters)', $maxLength));
    }

    public static function becauseItIsNotValid(): self
    {
        return new self('Locale is not valid');
    }
}
