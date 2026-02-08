<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidCategoryUlidException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidUlid(): self
    {
        return new self('Invalid category ULID');
    }
}
