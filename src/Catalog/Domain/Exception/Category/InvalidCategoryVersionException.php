<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidCategoryVersionException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidVersion(): self
    {
        return new self('Category version must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_CATEGORY_VERSION';
    }
}
