<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidAttributeVersionException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('Attribute version must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_ATTRIBUTE_VERSION';
    }
}
