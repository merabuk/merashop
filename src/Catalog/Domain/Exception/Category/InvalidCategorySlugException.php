<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidCategorySlugException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Category slug cannot be empty');
    }

    public static function becauseItDoesNotMatchRegex(): self
    {
        return new self('Category slug does not match regex pattern');
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Category slug cannot be longer than %d characters', $maxLength));
    }

    public function getErrorCode(): string
    {
        return 'INVALID_CATEGORY_SLUG';
    }
}
