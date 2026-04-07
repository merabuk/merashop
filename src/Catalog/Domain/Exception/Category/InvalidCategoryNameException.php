<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Shared\Domain\Exception\Services\Validation\InvalidStringException;

final class InvalidCategoryNameException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(string $locale): self
    {
        return new self(sprintf('Category name is required for locale: %s', $locale));
    }

    public static function fromBaseException(InvalidStringException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
