<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductVersionException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidVersion(): self
    {
        return new self('Product version must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_CATEGORY_VERSION';
    }
}
