<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductSkuException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Product SKU cannot be empty');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_SKU';
    }
}
