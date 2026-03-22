<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\AttributeOption;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidAttributeOptionIdException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('Attribute option ID must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_ATTRIBUTE_OPTION_ID';
    }
}
