<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidCategoryIdException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('Category ID must be a positive integer.');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_CATEGORY_ID';
    }
}
