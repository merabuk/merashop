<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductUlidException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidUlid(): self
    {
        return new self('Invalid product ULID');
    }
}
