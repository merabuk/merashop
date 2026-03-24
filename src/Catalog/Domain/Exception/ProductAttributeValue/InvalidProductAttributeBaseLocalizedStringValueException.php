<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductAttributeValue;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Shared\Domain\Exception\Services\Validation\InvalidStringException;

final class InvalidProductAttributeBaseLocalizedStringValueException extends InvalidCatalogValueObjectException
{
    public static function becauseLocaleIsMissing(string $locale): self
    {
        return new self(sprintf('Locale "%s" is missing', $locale));
    }

    public static function becauseValueIsNotString(string $locale): self
    {
        return new self(sprintf('Value must be a string for locale "%s"', $locale));
    }

    public static function fromBaseException(InvalidStringException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
