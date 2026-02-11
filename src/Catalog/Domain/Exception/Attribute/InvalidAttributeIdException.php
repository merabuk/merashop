<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidAttributeIdException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('Attribute ID must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_ATTRIBUTE_ID';
    }
}
