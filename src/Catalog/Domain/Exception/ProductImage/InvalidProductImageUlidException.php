<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductImage;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductImageUlidException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidUlid(): self
    {
        return new self('Invalid product image ULID');
    }
}
