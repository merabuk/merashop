<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidAttributeCodeException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Attribute code cannot be empty');
    }

    public static function becauseItIsTooLong(): self
    {
        return new self('Attribute code cannot be longer than 255 characters');
    }

    public static function becauseItDoesNotMatchRegex(): self
    {
        return new self('Attribute code does not match regex pattern');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_ATTRIBUTE_CODE';
    }
}
