<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductAttribute;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductAttributeIdException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('Product Attribute ID must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_ATTRIBUTE_ID';
    }
}
