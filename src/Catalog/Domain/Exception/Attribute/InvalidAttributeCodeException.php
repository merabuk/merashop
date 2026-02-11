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

    public function getErrorCode(): string
    {
        return 'INVALID_ATTRIBUTE_CODE';
    }
}
