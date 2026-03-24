<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\AttributeOption;

use App\Catalog\Domain\Exception\CatalogDomainException;

final class UnsupportedAttributeOptionMetadataTypeException extends CatalogDomainException
{
    public static function becauseIsItNotSupportedType(string $type): self
    {
        return new self(sprintf('Unsupported Product attribute value type: %s', $type));
    }
}
