<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class AttributeOptionUniqueException extends InvalidCatalogValueObjectException
{
    public static function becauseDuplicateOption(string $value, string $property): self
    {
        return new self(message: sprintf('Attribute option with %s "%s" already exists', $property, $value));
    }

    public function getErrorCode(): string
    {
        return 'ATTRIBUTE_OPTION_UNIQUE_EXCEPTION';
    }
}
