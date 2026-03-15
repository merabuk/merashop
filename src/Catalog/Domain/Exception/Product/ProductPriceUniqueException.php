<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\CatalogDomainException;

class ProductPriceUniqueException extends CatalogDomainException
{
    public static function duplicatePriceTypeForCurrency(string $priceType, string $currency): self
    {
        return new self(sprintf("Product price type '%s' for currency '%s' already exists", $priceType, $currency));
    }

    public function getErrorCode(): string
    {
        return 'PRODUCT_PRICE_UNIQUE_EXCEPTION';
    }
}
