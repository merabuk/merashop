<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

final class InvalidLocaleException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Locale cannot be empty');
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Locale is too long (max %d characters)', $maxLength));
    }
}
