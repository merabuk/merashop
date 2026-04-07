<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\AttributeOption;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidAttributeOptionVersionException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidVersion(): self
    {
        return new self('Attribute option version must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_ATTRIBUTE_OPTION_VERSION';
    }
}
