<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductIdException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('Product ID must be a positive integer.');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_ID';
    }
}
