<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidCategorySlugException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Category slug cannot be empty.');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_CATEGORY_SLUG';
    }
}
