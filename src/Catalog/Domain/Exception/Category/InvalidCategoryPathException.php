<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidCategoryPathException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Category path cannot be empty');
    }

    public static function becauseItContainsInvalidCharacters(): self
    {
        return new self('Category path cannot contain invalid characters');
    }

    public static function becauseItDoesNotStartWithSeparator(string $separator): self
    {
        return new self(sprintf('Category path must start with separator "%s"', $separator));
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Category path cannot be longer than %d characters', $maxLength));
    }

    public function getErrorCode(): string
    {
        return 'INVALID_CATEGORY_PATH';
    }
}
