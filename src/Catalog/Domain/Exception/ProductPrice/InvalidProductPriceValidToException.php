<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductPrice;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use Throwable;

class InvalidProductPriceValidToException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotValidDateTimeString(Throwable $e): self
    {
        return new self('Product price valid to invalid date format', previous: $e);
    }
}
