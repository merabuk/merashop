<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidCategoryStatusException extends InvalidCatalogValueObjectException
{
    /**
     * @param string[] $availableValues
     */
    public static function becauseItIsNotAValidStatus(string $invalidValue, array $availableValues): self
    {
        return new self(sprintf(
            '"%s" is not a valid Category status. Available statuses: %s',
            $invalidValue,
            implode(', ', $availableValues)
        ));
    }
}
