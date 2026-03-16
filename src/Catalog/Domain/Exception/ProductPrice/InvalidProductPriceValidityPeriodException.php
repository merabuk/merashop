<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductPrice;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductPriceValidityPeriodException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsInvalidValidityPeriod(
        string $fromField,
        string $toField,
    ): self {
        return new self(sprintf('Validity period property %s must be before than %s', $fromField, $toField));
    }
}
