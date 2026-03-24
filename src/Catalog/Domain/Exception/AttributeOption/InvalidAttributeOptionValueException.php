<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\AttributeOption;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Shared\Domain\Exception\InvalidStringException;

final class InvalidAttributeOptionValueException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(string $locale): self
    {
        return new self(sprintf('Attribute option value is required for locale: %s', $locale));
    }

    public static function fromBaseException(InvalidStringException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
