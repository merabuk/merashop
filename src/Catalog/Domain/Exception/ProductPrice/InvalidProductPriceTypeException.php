<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductPrice;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductPriceTypeException extends InvalidCatalogValueObjectException
{
    /**
     * @param string[] $availableValues
     */
    public static function becauseItIsNotAValidType(string $invalidValue, array $availableValues): self
    {
        return new self(sprintf(
            '"%s" is not a valid Product price type. Available types: %s',
            $invalidValue,
            implode(', ', $availableValues)
        ));
    }
}
