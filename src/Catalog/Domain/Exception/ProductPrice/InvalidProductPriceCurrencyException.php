<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductPrice;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductPriceCurrencyException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Product price currency cannot be empty');
    }

    public static function becauseItIsNotAValidCurrencyCode(): self
    {
        return new self('Product price currency must be a 3-letter ISO code');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_PRICE_CURRENCY';
    }
}
