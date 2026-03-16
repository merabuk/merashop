<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Shared\Domain\Exception\ValueObject\InvalidAbstractCollectionItemException;

class InvalidProductPriceItemException extends InvalidCatalogValueObjectException
{
    public static function fromBase(InvalidAbstractCollectionItemException $e): self
    {
        return new self(message: $e->getMessage(), previous: $e);
    }
}
