<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductPrice;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductPriceTaxValueException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidTax(): self
    {
        return new self('Product price Tax must be a positive integer');
    }

    public static function becauseItIsNotAValidPercentageValue(float $invalidValue): self
    {
        return new self(sprintf('Product price Tax value must be a valid percentage value. Given: %06.3f', $invalidValue));
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_PRICE_TAX';
    }
}
