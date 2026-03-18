<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

class ProductImagesMainImageException extends InvalidCatalogValueObjectException
{
    public static function becauseNoMainImage(): self
    {
        return new self(message: 'Product must have at least one main image');
    }

    public static function becauseTooManyMainImages(int $maxMainImagesCount): self
    {
        return new self(message: sprintf('Product can have only %d main image(s)', $maxMainImagesCount));
    }
}
