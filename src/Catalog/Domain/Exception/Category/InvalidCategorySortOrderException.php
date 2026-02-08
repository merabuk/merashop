<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidCategorySortOrderException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidSortOrder(): self
    {
        return new self('Category sort order must be an integer.');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_CATEGORY_SORT_ORDER';
    }
}
