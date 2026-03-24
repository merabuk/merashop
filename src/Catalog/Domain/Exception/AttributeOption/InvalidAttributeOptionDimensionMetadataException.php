<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\AttributeOption;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidAttributeOptionDimensionMetadataException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidBaseRatio(): self
    {
        return new self('The base ratio must be a positive number');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_ATTRIBUTE_OPTION_DIMENSION_METADATA';
    }
}
