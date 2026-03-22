<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class ProductPricesEmptyException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Product prices is empty');
    }
}
