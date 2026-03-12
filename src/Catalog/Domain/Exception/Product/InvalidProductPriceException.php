<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductPriceException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotValid(): self
    {
        return new self('Invalid product price');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_PRICE';
    }
}
