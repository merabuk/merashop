<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductPrice;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductPriceVersionException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidVersion(): self
    {
        return new self('Product price version must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_PRICE_VERSION';
    }
}
