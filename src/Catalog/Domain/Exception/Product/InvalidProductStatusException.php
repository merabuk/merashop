<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductStatusException extends InvalidCatalogValueObjectException
{
    /**
     * @param string[] $availableValues
     */
    public static function becauseItIsNotAValidStatus(string $invalidValue, array $availableValues): self
    {
        return new self(sprintf(
            '"%s" is not a valid Product status. Available statuses: %s',
            $invalidValue,
            implode(', ', $availableValues)
        ));
    }
}
