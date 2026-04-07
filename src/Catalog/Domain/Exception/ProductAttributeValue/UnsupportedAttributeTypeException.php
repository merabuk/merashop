<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductAttributeValue;

use App\Catalog\Domain\Exception\CatalogDomainException;

final class UnsupportedAttributeTypeException extends CatalogDomainException
{
    public static function becauseIsItNotSupportedType(string $type): self
    {
        return new self(sprintf('Unsupported Product attribute value type: %s', $type));
    }
}
