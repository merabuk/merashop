<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductAttributeValue;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductAttributeMagnitudeDimensionValueException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidMagnitude(): self
    {
        return new self('The magnitude cannot be negative');
    }
}
