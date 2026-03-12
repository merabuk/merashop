<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductSkuException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Product SKU cannot be empty');
    }

    public static function becauseItIsInvalidFormat(): self
    {
        return new self('Product SKU must contain only alphanumeric characters and hyphens');
    }

    public static function becauseToShort(int $minLength): self
    {
        return new self(sprintf('Product SKU must be at least %d characters long', $minLength));
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Product SKU cannot be longer than %d characters', $maxLength));
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_SKU';
    }
}
