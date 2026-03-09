<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductImage;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductImageIdException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('Product image ID must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_IMAGE_ID';
    }
}
