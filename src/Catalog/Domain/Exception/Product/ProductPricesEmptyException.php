<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\CatalogDomainException;

class ProductPricesEmptyException extends CatalogDomainException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Product prices is empty');
    }
}
