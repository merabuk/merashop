<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductAttributeValue;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductAttributeValueVersionException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidVersion(): self
    {
        return new self('Product attribute value version must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_ATTRIBUTE_VALUE_VERSION';
    }
}
