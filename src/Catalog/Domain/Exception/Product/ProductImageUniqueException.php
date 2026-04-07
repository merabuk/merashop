<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class ProductImageUniqueException extends InvalidCatalogValueObjectException
{
    public static function becauseDuplicateImage(string $ulid): self
    {
        return new self(message: sprintf('Product image with ulid "%s" already exists', $ulid));
    }

    public function getErrorCode(): string
    {
        return 'PRODUCT_IMAGE_UNIQUE_EXCEPTION';
    }
}
