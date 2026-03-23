<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductAttributeValue;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductAttributeColorValueException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidHex(string $value): self
    {
        return new self(sprintf('The value "%s" is not a valid HEX color code', $value));
    }
}
