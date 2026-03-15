<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductPrice;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use Throwable;

final class InvalidProductPriceValidFromException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotValidDateTimeString(Throwable $e): self
    {
        return new self('Product price valid from invalid date format', previous: $e);
    }
}
